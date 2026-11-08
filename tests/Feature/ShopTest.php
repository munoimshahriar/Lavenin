<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'category_id' => Category::create(['name' => ['en' => 'Soft', 'bn' => 'নরম']])->id,
            'name' => ['en' => 'Bunny', 'bn' => 'খরগোশ'],
            'price' => 600,
            'stock' => 10,
        ], $attrs));
    }

    private function checkoutData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Rahim',
            'phone' => '01712345678',
            'district' => 'Dhaka',
            'city' => 'Mirpur',
            'address' => 'House 1, Road 2',
            'payment_method' => 'cod',
        ], $overrides);
    }

    public function test_storefront_pages_render(): void
    {
        $p = $this->product();

        $this->get('/')->assertOk()->assertSee('খরগোশ');
        $this->get('/shop')->assertOk()->assertSee('খরগোশ');
        $this->get('/shop?q=Bunny')->assertOk()->assertSee('খরগোশ');
        $this->get('/category/'.$p->category->slug)->assertOk();
        $this->get('/product/'.$p->slug)->assertOk()->assertSee('application/ld+json', false)->assertSee('"priceCurrency":"BDT"', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('/product/'.$p->slug);
        $this->get('/contact')->assertOk();
    }

    public function test_slug_is_generated_from_english_name(): void
    {
        $this->assertSame('bunny', $this->product()->slug);
        $this->assertSame('bunny-2', $this->product()->slug);
    }

    public function test_inactive_product_is_hidden(): void
    {
        $p = $this->product(['is_active' => false]);
        $this->get('/product/'.$p->slug)->assertNotFound();
    }

    public function test_guest_checkout_places_order_with_server_side_totals(): void
    {
        $p = $this->product();

        $this->post('/cart/'.$p->slug, ['quantity' => 2])->assertRedirect();

        // A tampered price/total in the request must be ignored.
        $res = $this->post('/checkout', $this->checkoutData(['total' => 1, 'price' => 1]));

        $order = Order::firstOrFail();
        $res->assertRedirect('/order/'.$order->number);
        $this->assertSame(1200, $order->subtotal);
        $this->assertSame(70, $order->shipping);
        $this->assertSame(1270, $order->total);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Unpaid, $order->payment_status);
        $this->assertSame('খরগোশ', $order->items->first()->name);

        $this->get('/order/'.$order->number)->assertOk()->assertSee($order->number);
        $this->get('/cart')->assertSee(__('shop.cart.empty', [], 'bn'));
    }

    public function test_other_visitors_cannot_view_an_order(): void
    {
        $p = $this->product();
        $this->post('/cart/'.$p->slug);
        $this->post('/checkout', $this->checkoutData());
        $number = Order::first()->number;

        $this->flushSession();
        $this->get('/order/'.$number)->assertNotFound();
    }

    public function test_bkash_requires_trx_and_sets_pending_verification(): void
    {
        Setting::write(['bkash_number' => '01811111111']);
        $p = $this->product(['price' => 2500]);
        $this->post('/cart/'.$p->slug);

        $this->post('/checkout', $this->checkoutData(['payment_method' => 'bkash']))
            ->assertSessionHasErrors(['sender_number', 'trx_id']);

        $this->post('/checkout', $this->checkoutData(['payment_method' => 'bkash', 'sender_number' => '01999999999', 'trx_id' => 'abc12345xy']))
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(0, $order->shipping);
        $this->assertSame(PaymentStatus::PendingVerification, $order->payment_status);
        $this->assertSame('ABC12345XY', $order->trx_id);
    }

    public function test_bkash_is_unavailable_without_merchant_number(): void
    {
        $p = $this->product();
        $this->post('/cart/'.$p->slug);
        $this->post('/checkout', $this->checkoutData(['payment_method' => 'bkash', 'sender_number' => '01999999999', 'trx_id' => 'ABC12345']))
            ->assertSessionHasErrors('payment_method');
    }

    public function test_invalid_phone_and_honeypot_are_rejected(): void
    {
        $p = $this->product();
        $this->post('/cart/'.$p->slug);
        $this->post('/checkout', $this->checkoutData(['phone' => '12345']))->assertSessionHasErrors('phone');
        $this->post('/checkout', $this->checkoutData(['website' => 'spam']))->assertSessionHasErrors('website');
        $this->assertSame(0, Order::count());
    }

    public function test_cannot_order_more_than_stock(): void
    {
        $p = $this->product(['stock' => 1]);
        $this->post('/cart/'.$p->slug, ['quantity' => 1]);
        $p->update(['stock' => 0]);

        $this->post('/checkout', $this->checkoutData())->assertSessionHasErrors('cart');
        $this->assertSame(0, Order::count());
    }

    public function test_coupon_discount_and_usage(): void
    {
        Coupon::create(['code' => 'baby10', 'type' => 'percent', 'value' => 10, 'min_order' => 500, 'usage_limit' => 1]);
        $p = $this->product(['price' => 1000]);
        $this->post('/cart/'.$p->slug);

        $this->getJson('/checkout/quote?district=Khulna&coupon=BABY10')
            ->assertJson(['subtotal' => 1000, 'discount' => 100, 'shipping' => 0, 'total' => 900, 'coupon_applied' => true]);

        $this->post('/checkout', $this->checkoutData(['coupon' => 'baby10', 'district' => 'Khulna']))->assertRedirect();
        $this->assertSame(900, Order::first()->total);
        $this->assertSame(1, Coupon::first()->used_count);

        // Usage limit reached.
        $this->post('/cart/'.$p->slug);
        $this->post('/checkout', $this->checkoutData(['coupon' => 'BABY10']))->assertSessionHasErrors('coupon');
    }

    public function test_stock_moves_on_confirm_and_cancel(): void
    {
        $p = $this->product(['stock' => 5]);
        $this->post('/cart/'.$p->slug, ['quantity' => 2]);
        $this->post('/checkout', $this->checkoutData());
        $order = Order::first();

        $this->assertSame(5, $p->fresh()->stock);
        $order->transitionTo(OrderStatus::Confirmed);
        $this->assertSame(3, $p->fresh()->stock);
        $order->transitionTo(OrderStatus::Cancelled);
        $this->assertSame(5, $p->fresh()->stock);

        $this->expectException(\InvalidArgumentException::class);
        $order->transitionTo(OrderStatus::Shipped);
    }

    public function test_cod_is_marked_paid_on_delivery(): void
    {
        $p = $this->product();
        $this->post('/cart/'.$p->slug);
        $this->post('/checkout', $this->checkoutData());
        $order = Order::first();

        foreach ([OrderStatus::Confirmed, OrderStatus::Packed, OrderStatus::Shipped, OrderStatus::Delivered] as $s) {
            $order->transitionTo($s);
        }
        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
    }

    public function test_register_login_with_phone_and_cart_merge(): void
    {
        $p = $this->product();

        $this->post('/register', [
            'name' => 'Karim', 'phone' => '+8801712345678', 'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertRedirect('/account');
        $this->assertSame('01712345678', User::first()->phone);
        $this->post('/logout');

        // Guest adds to cart, then logs in with phone: cart is kept.
        $this->post('/cart/'.$p->slug, ['quantity' => 3]);
        $this->post('/login', ['login' => '01712345678', 'password' => 'secret123'])->assertRedirect('/account');
        $this->get('/cart')->assertSee('খরগোশ');
        $this->get('/account')->assertOk();
    }

    public function test_login_is_rate_limited(): void
    {
        User::create(['name' => 'A', 'phone' => '01712345678', 'password' => 'secret123']);

        foreach (range(1, 5) as $i) {
            $this->post('/login', ['login' => '01712345678', 'password' => 'wrong-pass']);
        }
        $this->post('/login', ['login' => '01712345678', 'password' => 'secret123'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_only_buyers_can_review(): void
    {
        $p = $this->product();
        $user = User::create(['name' => 'B', 'phone' => '01712345678', 'password' => 'secret123']);

        $this->actingAs($user)->post("/product/{$p->slug}/reviews", ['rating' => 5])->assertSessionHasErrors('review');

        $order = Order::create([
            'number' => 'LVTEST', 'user_id' => $user->id, 'status' => 'delivered', 'name' => 'B', 'phone' => '01712345678',
            'district' => 'Dhaka', 'city' => 'X', 'address' => 'Y', 'subtotal' => 600, 'shipping' => 70, 'total' => 670,
            'payment_method' => 'cod', 'payment_status' => 'paid',
        ]);
        $order->items()->create(['product_id' => $p->id, 'name' => 'Bunny', 'price' => 600, 'quantity' => 1]);

        $this->actingAs($user)->post("/product/{$p->slug}/reviews", ['rating' => 4, 'body' => 'Lovely'])->assertSessionHas('status');
        $this->assertFalse($p->reviews()->first()->is_approved);
        $this->get('/product/'.$p->slug)->assertDontSee('Lovely');

        $p->reviews()->update(['is_approved' => true]);
        $this->get('/product/'.$p->slug)->assertSee('Lovely');
    }

    public function test_wishlist_toggle(): void
    {
        $p = $this->product();
        $user = User::create(['name' => 'C', 'phone' => '01712345679', 'password' => 'secret123']);

        $this->actingAs($user)->post('/wishlist/'.$p->slug);
        $this->get('/wishlist')->assertSee('খরগোশ');
        $this->post('/wishlist/'.$p->slug);
        $this->assertSame(0, $user->wishlist()->count());
    }

    private function admin(string $role): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::create(['name' => $role, 'email' => "{$role}@example.com", 'password' => 'secret123']);
        $user->assignRole($role);

        return $user;
    }

    public function test_customers_cannot_open_admin(): void
    {
        $customer = User::create(['name' => 'Cust', 'email' => 'c@example.com', 'password' => 'secret123']);
        $this->actingAs($customer)->get('/manage')->assertForbidden();
    }

    public function test_staff_can_only_manage_orders(): void
    {
        $staff = $this->admin('staff');
        $this->actingAs($staff)->get('/manage/orders')->assertOk();
        $this->actingAs($staff)->get('/manage/products')->assertForbidden();
        $this->actingAs($staff)->get('/manage/settings')->assertForbidden();
    }

    public function test_manager_cannot_manage_admin_users(): void
    {
        $manager = $this->admin('manager');
        $this->actingAs($manager)->get('/manage/products')->assertOk();
        $this->actingAs($manager)->get('/manage/users')->assertForbidden();
        $this->actingAs($manager)->get('/manage/settings')->assertForbidden();
    }

    public function test_security_headers_present(): void
    {
        $res = $this->get('/');
        $res->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $res->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString("frame-ancestors 'self'", $res->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('youtube-nocookie.com', $res->headers->get('Content-Security-Policy'));
    }
}
