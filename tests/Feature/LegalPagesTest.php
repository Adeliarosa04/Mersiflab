<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_terms_page_opens()
    {
        $this->get('/syarat-ketentuan')
            ->assertOk()
            ->assertSee('Terms and Conditions');
    }

    public function test_privacy_page_opens()
    {
        $this->get('/privacy-policy')
            ->assertOk()
            ->assertSee('Privacy Policy');
    }

    public function test_footer_links_point_to_working_routes()
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('terms'))
            ->assertSee(route('privacy'));
    }

    public function test_signup_page_links_to_legal_pages()
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee(route('terms'))
            ->assertSee(route('privacy'));
    }

    public function test_admin_supplied_content_is_rendered_when_available()
    {
        Setting::set('terms_content', '<p>Naskah resmi syarat dan ketentuan.</p>');

        $this->get('/syarat-ketentuan')
            ->assertOk()
            ->assertSee('Naskah resmi syarat dan ketentuan.');
    }
}
