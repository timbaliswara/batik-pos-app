<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_directory_is_publicly_accessible(): void
    {
        $product = Product::query()->create([
            'code' => 'BTK-PBL-001',
            'name' => 'Batik Publik',
            'type' => Product::TYPE_CLOTHES,
            'best_seller' => true,
            'low_stock_threshold' => 3,
        ]);

        ProductStock::query()->create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 12,
        ]);

        $this->get('/stock-directory')
            ->assertOk()
            ->assertSeeText('Daftar Motif, Ukuran, dan Stok')
            ->assertSeeText('Batik Publik')
            ->assertSeeText('Best Seller');
    }

    public function test_stock_directory_pdf_can_be_downloaded_without_login(): void
    {
        $this->get('/stock-directory/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
