<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockIn;
use App\Models\StockOut;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function syncProductStocks(Product $product): void
    {
        $allowedSizes = $product->availableSizes();

        DB::transaction(function () use ($product, $allowedSizes): void {
            foreach ($allowedSizes as $size) {
                ProductStock::query()->firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'size' => $size,
                    ],
                    [
                        'stock' => 0,
                    ],
                );
            }

            ProductStock::query()
                ->where('product_id', $product->id)
                ->whereNotIn('size', $allowedSizes)
                ->delete();
        });
    }

    public function recordStockIn(Product $product, string $size, int $quantity, array $payload = []): StockIn
    {
        return DB::transaction(function () use ($product, $size, $quantity, $payload): StockIn {
            $stock = $this->stockRow($product, $size);
            $stock->increment('stock', $quantity);

            return StockIn::query()->create([
                'product_id' => $product->id,
                'size' => $size,
                'quantity' => $quantity,
                'reference' => $payload['reference'] ?? null,
                'note' => $payload['note'] ?? null,
                'transaction_date' => $payload['transaction_date'],
            ]);
        });
    }

    public function recordStockOut(Product $product, string $size, int $quantity, array $payload = []): StockOut
    {
        return DB::transaction(function () use ($product, $size, $quantity, $payload): StockOut {
            $stock = $this->stockRow($product, $size);

            if ($stock->stock < $quantity) {
                throw new RuntimeException('Stok tidak mencukupi untuk transaksi keluar.');
            }

            $stock->decrement('stock', $quantity);

            return StockOut::query()->create([
                'product_id' => $product->id,
                'size' => $size,
                'quantity' => $quantity,
                'transaction_number' => $payload['transaction_number'] ?? null,
                'buyer_name' => $payload['buyer_name'] ?? null,
                'note' => $payload['note'] ?? null,
                'transaction_date' => $payload['transaction_date'],
            ]);
        });
    }

    protected function stockRow(Product $product, string $size): ProductStock
    {
        if (! in_array($size, $product->availableSizes(), true)) {
            throw new RuntimeException('Ukuran tidak valid untuk produk ini.');
        }

        return ProductStock::query()->firstOrCreate(
            [
                'product_id' => $product->id,
                'size' => $size,
            ],
            [
                'stock' => 0,
            ],
        );
    }
}
