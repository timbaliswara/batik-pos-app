<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Support\InventoryService;
use Illuminate\Database\Seeder;

class DemoInventorySeeder extends Seeder
{
    public function run(): void
    {
        $inventory = app(InventoryService::class);
        $catalog = $this->catalog();

        foreach ($catalog as $row) {
            $product = Product::query()->create([
                'code' => $row['code'],
                'name' => $row['name'],
                'type' => $row['type'],
                'description' => $row['description'],
                'price' => $row['price'],
                'best_seller' => $row['best_seller'],
                'low_stock_threshold' => $row['low_stock_threshold'],
            ]);

            $inventory->syncProductStocks($product);

            foreach ($row['incoming'] as $size => $quantity) {
                $inventory->recordStockIn($product, $size, $quantity, [
                    'reference' => 'INIT-'.$product->code,
                    'note' => 'Seed data stok awal',
                    'transaction_date' => today()->subDays(7)->toDateString(),
                ]);
            }

            foreach ($row['outgoing'] as $size => $quantity) {
                $inventory->recordStockOut($product, $size, $quantity, [
                    'transaction_number' => 'OUT-'.$product->code,
                    'buyer_name' => 'Pelanggan Demo',
                    'note' => 'Seed data transaksi keluar',
                    'transaction_date' => today()->subDays(2)->toDateString(),
                ]);
            }
        }
    }

    protected function catalog(): array
    {
        $clothingMotifs = [
            'Parang', 'Kawung', 'Sekar Jagad', 'Truntum', 'Sidomukti',
            'Lasem', 'Cendrawasih', 'Lereng', 'Ceplok', 'Tambal',
        ];
        $clothingStyles = [
            'Premium', 'Heritage', 'Nusantara', 'Signature', 'Royal',
            'Klasik', 'Eksklusif', 'Modern', 'Santai', 'Formal',
        ];
        $fabricMotifs = [
            'Mega Mendung', 'Pesisir', 'Sogan', 'Flora Jawa', 'Wadasan',
            'Tirta Teja', 'Rinjani', 'Kenongo', 'Buketan', 'Paksi Naga',
        ];
        $fabricSeries = [
            'Artisan', 'Heritage', 'Luxe', 'Signature', 'Klasik',
            'Premium', 'Eksplorasi', 'Anggun', 'Mewah', 'Utama',
        ];
        $descriptors = [
            'untuk koleksi premium', 'bernuansa klasik elegan', 'dengan detail motif yang tegas',
            'untuk tampilan formal modern', 'yang nyaman dipakai harian', 'cocok untuk koleksi eksklusif',
            'dengan karakter warna yang hangat', 'untuk pelanggan yang menyukai motif ikonik',
        ];

        $catalog = [];

        for ($i = 1; $i <= 70; $i++) {
            $motif = $clothingMotifs[($i - 1) % count($clothingMotifs)];
            $style = $clothingStyles[intdiv($i - 1, count($clothingMotifs)) % count($clothingStyles)];
            $code = sprintf('BTK-%s-%03d', strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $motif), 0, 3)), $i);
            $name = 'Batik '.$motif.' '.$style;
            $description = 'Kemeja batik motif '.$motif.' '.$descriptors[($i - 1) % count($descriptors)].'.';
            $incoming = [];
            $outgoing = [];

            foreach (Product::CLOTHING_SIZES as $index => $size) {
                $base = 5 + (($i + $index) % 8);
                $sold = ($i + ($index * 2)) % max(2, $base - 1);
                $incoming[$size] = $base + 4;
                $outgoing[$size] = min($sold, $incoming[$size] - 1);
            }

            $catalog[] = [
                'code' => $code,
                'name' => $name,
                'type' => Product::TYPE_CLOTHES,
                'description' => $description,
                'price' => 215000 + (($i % 9) * 18000),
                'best_seller' => $i <= 18,
                'low_stock_threshold' => 3 + ($i % 4),
                'incoming' => $incoming,
                'outgoing' => $outgoing,
            ];
        }

        for ($i = 71; $i <= 100; $i++) {
            $sequence = $i - 70;
            $motif = $fabricMotifs[($sequence - 1) % count($fabricMotifs)];
            $series = $fabricSeries[intdiv($sequence - 1, count($fabricMotifs)) % count($fabricSeries)];
            $code = sprintf('KAIN-%s-%03d', strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $motif), 0, 3)), $i);
            $name = 'Kain Batik '.$motif.' '.$series;
            $incomingStock = 18 + (($sequence * 3) % 29);
            $outgoingStock = min(14, 3 + ($sequence % 11));

            $catalog[] = [
                'code' => $code,
                'name' => $name,
                'type' => Product::TYPE_FABRIC,
                'description' => 'Kain batik meteran motif '.$motif.' untuk kebutuhan custom order dan stock opname manual.',
                'price' => 145000 + (($sequence % 8) * 12000),
                'best_seller' => $sequence <= 7,
                'low_stock_threshold' => 8 + ($sequence % 6),
                'incoming' => ['NONE' => $incomingStock],
                'outgoing' => ['NONE' => $outgoingStock],
            ];
        }

        return $catalog;
    }
}
