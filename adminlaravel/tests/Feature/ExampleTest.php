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

    public function test_unauthenticated_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
