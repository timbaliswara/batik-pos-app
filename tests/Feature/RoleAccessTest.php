<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_cannot_access_inventory_transaction_pages(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VIEWER,
        ]);

        $this->actingAs($user)->get('/stock-in')->assertForbidden();
        $this->actingAs($user)->get('/stock-out')->assertForbidden();
    }

    public function test_viewer_can_still_access_dashboard_products_and_reports(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VIEWER,
        ]);

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/products')->assertOk();
        $this->actingAs($user)->get('/reports')->assertOk();
    }

    public function test_guest_can_access_public_products_page_only(): void
    {
        $this->get('/products')->assertOk();
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/products/create')->assertRedirect('/login');
    }

    public function test_cashier_can_access_inventory_transaction_pages(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CASHIER,
        ]);

        $this->actingAs($user)->get('/stock-in')->assertOk();
        $this->actingAs($user)->get('/stock-out')->assertOk();
    }
}
