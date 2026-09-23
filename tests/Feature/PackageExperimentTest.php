<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PackageExperimentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_are_redirected_to_login()
    {
        $response = $this->get(route('experimental.packages.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_users_can_access_experimental_sandbox()
    {
        $user = User::factory()->make([
            'id' => 1,
            'first_name' => 'Sandbox',
            'last_name' => 'Tester',
            'is_system_admin' => true,
        ]);

        $response = $this->actingAs($user)->get(route('experimental.packages.index'));

        $response->assertStatus(200);
        $response->assertSee('SANDBOX EXPERIMENT');
        $response->assertSee('Noubtigo Simple');
        $response->assertSee('Noubtigo Queue');
        $response->assertSee('Noubtigo Queue+');
        $response->assertSee('WhatsApp Add-on');
        $response->assertSee('Display Add-on');
        $response->assertSee('Notifications Add-on');
        $response->assertSee('Advanced Analytics Add-on');
        $response->assertSee('API / Integrations Add-on');
        $response->assertSee('Capabilities Comparison Matrix');
    }

    #[Test]
    public function experimental_area_returns_404_when_feature_flag_is_disabled()
    {
        config(['app.package_experiment' => false]);

        $user = User::factory()->make([
            'id' => 1,
            'first_name' => 'Sandbox',
            'last_name' => 'Tester',
        ]);

        $response = $this->actingAs($user)->get(route('experimental.packages.index'));

        $response->assertStatus(404);
    }
}
