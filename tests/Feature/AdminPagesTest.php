<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_must_set_up_2fa_then_can_open_every_admin_page(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $owner = User::role('owner')->firstOrFail();

        $this->actingAs($owner)->get('/manage')->assertRedirect('/manage/profile');

        $owner->enableTwoFactorAuthentication();
        $owner->confirmTwoFactorAuthentication();
        $owner->setTwoFactorSession();

        $this->post('/cart/'.Product::first()->slug);
        $this->post('/checkout', ['name' => 'A', 'phone' => '01712345678', 'district' => 'Dhaka', 'city' => 'X', 'address' => 'Y', 'payment_method' => 'cod']);
        $number = Order::firstOrFail()->number;

        foreach ([
            '/manage', '/manage/settings', '/manage/users', '/manage/products', '/manage/products/create',
            '/manage/products/'.Product::first()->slug.'/edit', '/manage/categories', '/manage/categories/'.Category::first()->slug.'/edit',
            '/manage/orders', "/manage/orders/{$number}", "/manage/orders/{$number}/slip", '/manage/coupons', '/manage/reviews', '/manage/profile',
        ] as $url) {
            $this->actingAs($owner)->get($url)->assertOk();
        }
    }
}
