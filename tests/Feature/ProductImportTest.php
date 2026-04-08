<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Support\ProductSpreadsheetImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_service_can_create_and_update_products_with_stock_opname(): void
    {
        Product::query()->create([
            'code' => 'BTK-OLD-001',
            'name' => 'Produk Lama',
            'type' => Product::TYPE_CLOTHES,
            'description' => 'Deskripsi lama',
            'price' => 100000,
            'best_seller' => false,
            'low_stock_threshold' => 2,
        ]);

        $csv = implode("\n", [
            'code,name,type,description,price,best_seller,low_stock_threshold,stock_s,stock_m,stock_l,stock_xl,stock_xxl,stock_none',
            'BTK-OLD-001,Produk Lama Update,baju,Deskripsi baru,125000,yes,4,3,4,5,6,7,0',
            'KAIN-NEW-002,Kain Baru,kain,Kain stock opname,175000,no,10,0,0,0,0,0,18',
        ]);

        $path = storage_path('app/private/test-import.csv');
        file_put_contents($path, $csv);

        $result = app(ProductSpreadsheetImportService::class)->import($path);

        $updated = Product::query()->where('code', 'BTK-OLD-001')->firstOrFail();
        $created = Product::query()->where('code', 'KAIN-NEW-002')->firstOrFail();

        $this->assertSame(['processed' => 2, 'created' => 1, 'updated' => 1], $result);
        $this->assertSame('Produk Lama Update', $updated->name);
        $this->assertTrue($updated->best_seller);
        $this->assertSame(3, $updated->stocks()->where('size', 'S')->value('stock'));
        $this->assertSame(18, $created->stocks()->where('size', 'NONE')->value('stock'));
    }

    public function test_viewer_cannot_access_import_page(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VIEWER,
        ]);

        $this->actingAs($user)->get('/products/import')->assertForbidden();
    }
}
