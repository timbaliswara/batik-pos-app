<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoicePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_access_invoice_page(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CASHIER,
        ]);

        $this->actingAs($user)
            ->get('/invoice')
            ->assertOk()
            ->assertSee('Buat Invoice Penjualan Langsung');
    }

    public function test_viewer_cannot_access_invoice_page(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VIEWER,
        ]);

        $this->actingAs($user)
            ->get('/invoice')
            ->assertForbidden();
    }

    public function test_invoice_product_search_shows_matching_results(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CASHIER,
        ]);

        Product::query()->create([
            'code' => 'BTK-PRG-001',
            'name' => 'Batik Parang Premium',
            'type' => Product::TYPE_CLOTHES,
            'price' => 250000,
            'best_seller' => false,
            'low_stock_threshold' => 2,
        ]);

        Livewire::actingAs($user)
            ->test('pages.invoice-page')
            ->set('productSearch', 'Parang')
            ->assertSee('Batik Parang Premium');
    }
}
