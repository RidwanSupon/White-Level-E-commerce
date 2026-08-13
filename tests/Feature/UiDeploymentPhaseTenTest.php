<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiDeploymentPhaseTenTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_pwa_manifest_is_accessible()
    {
        $response = $this->get('/manifest.json');

        $response->assertStatus(200);
        $response->assertJsonStructure(['name', 'short_name', 'start_url', 'display']);
    }

    public function test_storefront_layout_includes_pwa_manifest_and_meta_tags()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('manifest.json');
        $response->assertSee('apple-mobile-web-app-capable');
    }

    public function test_admin_can_access_white_label_settings_customizer()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('White-Label System Settings');
    }
}
