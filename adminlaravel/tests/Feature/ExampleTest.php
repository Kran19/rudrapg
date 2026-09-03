<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_authenticated_super_admin_can_access_dashboard(): void
    {
        $superAdmin = User::where('role', 'SUPER_ADMIN')->first();

        $response = $this->actingAs($superAdmin)->get('/super-admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_authenticated_sub_admin_can_access_dashboard(): void
    {
        $subAdmin = User::where('role', 'SUB_ADMIN')->first();

        $response = $this->actingAs($subAdmin)->get('/sub-admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Operational Dashboard');
    }

    public function test_unauthenticated_root_renders_login_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Sign In');
    }
}
