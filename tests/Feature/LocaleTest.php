<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_defaults_to_bangla(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('lang="bn"', false)
            ->assertSee(__('shop.shop_now', [], 'bn'));
    }

    public function test_switching_to_english(): void
    {
        $this->get('/lang/en')->assertRedirect();

        $this->get('/')
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('Shop now');
    }

    public function test_unsupported_locale_is_404(): void
    {
        $this->get('/lang/fr')->assertNotFound();
    }
}
