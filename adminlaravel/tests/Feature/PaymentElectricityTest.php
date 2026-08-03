<?php

namespace Tests\Feature;

use App\Models\ElectricityReading;
use App\Models\Payment;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentElectricityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_sub_admin_can_verify_payment(): void
    {
        // Sub Admin Login
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'subadmin.naroda@rudrapg.com',
            'password' => 'password',
        ]);

        $token = $loginRes->json('data.token');
        $payment = Payment::first();

        // Verify Payment
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/sub-admin/payments/'.$payment->id.'/verify');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'VERIFIED');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'VERIFIED',
        ]);
    }

    public function test_sub_admin_can_audit_electricity_reading(): void
    {
        // Sub Admin Login
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'subadmin.naroda@rudrapg.com',
            'password' => 'password',
        ]);

        $token = $loginRes->json('data.token');
        $reading = ElectricityReading::first();

        // Audit Electricity Reading
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/sub-admin/electricity-readings/'.$reading->id.'/audit');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'APPROVED');

        $this->assertDatabaseHas('electricity_readings', [
            'id' => $reading->id,
            'status' => 'APPROVED',
        ]);
    }
}
