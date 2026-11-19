<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetaPixelTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'name' => ['en' => 'Bunny', 'bn' => 'খরগোশ'],
            'description' => ['en' => '<p>Soft bunny</p>', 'bn' => '<p>নরম</p>'],
            'price' => 600,
            'sku' => 'LV-001',
            'stock' => 5,
        ], $attrs));
    }

    public function test_no_pixel_code_without_pixel_id(): void
    {
        $this->product();
        $this->get('/')->assertDontSee('fbevents.js', false);
    }

    public function test_pixel_and_shopping_events(): void
    {
        Setting::write(['fb_pixel_id' => '123456789012345', 'fb_domain_verification' => 'abc123def456ghi789']);
        $p = $this->product();

        $this->get('/')
            ->assertSee('fbevents.js', false)
            ->assertSee("fbq('init', \"123456789012345\")", false)
            ->assertSee('facebook-domain-verification', false);

        $this->get('/product/'.$p->slug)->assertSee('"ViewContent"', false)->assertSee('"LV-001"', false);
        $this->get('/shop?q=bunny')->assertSee('"Search"', false);

        // AddToCart fires once, on the page after the redirect.
        $this->post('/cart/'.$p->slug, ['quantity' => 2]);
        $this->get('/product/'.$p->slug)->assertSee('"AddToCart"', false)->assertSee('"value":1200', false);
        $this->get('/product/'.$p->slug)->assertDontSee('"AddToCart"', false);

        $this->get('/checkout')->assertSee('"InitiateCheckout"', false)->assertSee('"currency":"BDT"', false);

        $res = $this->post('/checkout', [
            'name' => 'Rahim', 'phone' => '01712345678', 'district' => 'Dhaka',
            'city' => 'Mirpur', 'address' => 'Road 1', 'payment_method' => 'cod',
        ]);
        $orderUrl = $res->headers->get('Location');

        // Purchase uses the server-side total (600×2 + 70 delivery) and fires only once.
        $this->get($orderUrl)->assertSee('"Purchase"', false)->assertSee('"value":1270', false);
        $this->get($orderUrl)->assertDontSee('"Purchase"', false);
    }

    public function test_meta_catalog_feed(): void
    {
        $p = $this->product(['price' => 450, 'compare_at_price' => 550]);
        ProductImage::create(['product_id' => $p->id, 'path' => 'products/a.webp', 'thumb_path' => 'products/thumbs/a.webp']);
        $this->product(['sku' => 'LV-002', 'name' => ['en' => 'No image', 'bn' => 'ছবি নেই']]);

        $res = $this->get('/feeds/meta.xml')->assertOk();
        $this->assertStringContainsString('application/xml', $res->headers->get('Content-Type'));

        $xml = simplexml_load_string($res->getContent());
        $items = $xml->channel->item;
        $this->assertCount(1, $items); // product without an image is skipped
        $g = $items[0]->children('http://base.google.com/ns/1.0');
        $this->assertSame('LV-001', (string) $g->id);
        $this->assertSame('550.00 BDT', (string) $g->price);
        $this->assertSame('450.00 BDT', (string) $g->sale_price);
        $this->assertSame('in stock', (string) $g->availability);
        $this->assertSame('Soft bunny', (string) $g->description);
    }
}
