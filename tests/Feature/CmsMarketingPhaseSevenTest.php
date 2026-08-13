<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsMarketingPhaseSevenTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_admin_can_create_and_publish_cms_page()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.pages.store'), [
            'title' => 'Terms of Service',
            'content' => 'Welcome to LuxeCart terms and conditions page.',
            'meta_title' => 'Terms of Service - LuxeCart',
        ]);

        $response->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseHas('pages', ['slug' => 'terms-of-service']);
    }

    public function test_customer_can_view_published_cms_page()
    {
        $page = Page::create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'content' => 'Your privacy is important to us.',
            'is_active' => true,
        ]);

        $response = $this->get('/page/privacy-policy');

        $response->assertStatus(200);
        $response->assertSee('Privacy Policy');
        $response->assertSee('Your privacy is important to us.');
    }

    public function test_xml_sitemap_returns_valid_xml()
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertSee('urlset');
    }

    public function test_robots_txt_returns_plain_text()
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertSee('User-agent: *');
        $response->assertSee('Disallow: /admin/');
    }

    public function test_customer_can_subscribe_to_newsletter()
    {
        $response = $this->post(route('newsletter.subscribe'), [
            'email' => 'subscriber@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'subscriber@example.com']);
    }
}
