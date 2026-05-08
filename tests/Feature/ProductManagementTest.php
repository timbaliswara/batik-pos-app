<?php

namespace Tests\Feature;

use App\Livewire\Pages\ProductsPage;
use App\Livewire\Pages\ProductFormPage;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_edit_existing_product(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CASHIER,
        ]);

        $product = Product::query()->create([
            'code' => 'BTK-OLD-001',
            'name' => 'Batik Lama',
            'type' => Product::TYPE_CLOTHES,
            'description' => 'Deskripsi lama',
            'low_stock_threshold' => 5,
        ]);

        Livewire::actingAs($user)
            ->test(ProductFormPage::class, ['productId' => $product->id])
            ->assertSet('productId', $product->id)
            ->set('name', 'Batik Baru')
            ->call('save')
            ->assertHasNoErrors();

        $product->refresh();

        $this->assertSame('Batik Baru', $product->name);
    }

    public function test_cashier_must_confirm_before_deleting_product(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CASHIER,
        ]);

        $product = Product::query()->create([
            'code' => 'BTK-DEL-001',
            'name' => 'Batik Hapus',
            'type' => Product::TYPE_CLOTHES,
            'description' => 'Akan dihapus',
            'price' => 99000,
            'low_stock_threshold' => 2,
        ]);

        Livewire::actingAs($user)
            ->test(ProductsPage::class)
            ->call('confirmDelete', $product->id)
            ->assertSet('pendingDeleteId', $product->id)
            ->assertSet('pendingDeleteName', 'Batik Hapus')
            ->call('delete')
            ->assertSet('pendingDeleteId', null)
            ->assertSet('pendingDeleteName', null);

        $this->assertNull($product->fresh());
    }

    public function test_cashier_can_toggle_best_seller_from_products_page(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CASHIER,
        ]);

        $product = Product::query()->create([
            'code' => 'BTK-BS-001',
            'name' => 'Batik Favorit',
            'type' => Product::TYPE_CLOTHES,
            'description' => 'Produk unggulan',
            'price' => 220000,
            'best_seller' => false,
            'low_stock_threshold' => 2,
        ]);

        Livewire::actingAs($user)
            ->test(ProductsPage::class)
            ->call('toggleBestSeller', $product->id);

        $this->assertTrue($product->fresh()->best_seller);
    }

    public function test_cashier_can_open_product_form_for_edit(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CASHIER,
        ]);

        $product = Product::query()->create([
            'code' => 'BTK-EDIT-002',
            'name' => 'Batik Form',
            'type' => Product::TYPE_FABRIC,
            'description' => 'Form edit terpisah',
            'price' => 200000,
            'best_seller' => true,
            'low_stock_threshold' => 3,
        ]);

        Livewire::actingAs($user)
            ->test(ProductFormPage::class, ['productId' => $product->id])
            ->assertSet('name', 'Batik Form')
            ->assertSet('best_seller', true);
    }

    public function test_cashier_can_edit_manual_stock_from_product_form(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CASHIER,
        ]);

        $product = Product::query()->create([
            'code' => 'BTK-STK-001',
            'name' => 'Batik Stok Manual',
            'type' => Product::TYPE_CLOTHES,
            'description' => 'Produk untuk uji edit stok manual',
            'low_stock_threshold' => 3,
        ]);

        foreach (Product::CLOTHING_SIZES as $size) {
            ProductStock::query()->create([
                'product_id' => $product->id,
                'size' => $size,
                'stock' => 0,
            ]);
        }

        Livewire::actingAs($user)
            ->test(ProductFormPage::class, ['productId' => $product->id])
            ->set('manualStocks.S', '11')
            ->set('manualStocks.M', '7')
            ->set('manualStocks.L', '5')
            ->set('manualStocks.XL', '3')
            ->set('manualStocks.XXL', '1')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(11, $product->stocks()->where('size', 'S')->value('stock'));
        $this->assertSame(7, $product->stocks()->where('size', 'M')->value('stock'));
        $this->assertSame(5, $product->stocks()->where('size', 'L')->value('stock'));
        $this->assertSame(3, $product->stocks()->where('size', 'XL')->value('stock'));
        $this->assertSame(1, $product->stocks()->where('size', 'XXL')->value('stock'));
    }
}
