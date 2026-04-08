<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductSpreadsheetImportService
{
    public function import(string $path): array
    {
        $rows = $this->rowsFromSpreadsheet($path);

        if ($rows === []) {
            throw new InvalidArgumentException('File import kosong atau tidak memiliki data.');
        }

        $summary = DB::transaction(function () use ($rows) {
            $created = 0;
            $updated = 0;

            foreach ($rows as $index => $row) {
                $normalized = $this->normalizeRow($row, $index + 2);
                $product = Product::query()->firstWhere('code', $normalized['code']) ?? new Product();

                $isNew = ! $product->exists;

                $product->fill([
                    'code' => $normalized['code'],
                    'name' => $normalized['name'],
                    'type' => $normalized['type'],
                    'description' => $normalized['description'],
                    'price' => $normalized['price'],
                    'best_seller' => $normalized['best_seller'],
                    'low_stock_threshold' => $normalized['low_stock_threshold'],
                ]);
                $product->save();

                $this->syncStockOpname($product, $normalized['stocks']);

                $isNew ? $created++ : $updated++;
            }

            return [
                'processed' => count($rows),
                'created' => $created,
                'updated' => $updated,
            ];
        });

        return $summary;
    }

    protected function rowsFromSpreadsheet(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        $header = array_shift($rows);

        if (! $header) {
            return [];
        }

        $headers = collect($header)
            ->map(fn ($value) => $this->normalizeHeader((string) $value))
            ->all();

        return collect($rows)
            ->filter(fn (array $row) => collect($row)->contains(fn ($value) => trim((string) $value) !== ''))
            ->map(function (array $row) use ($headers) {
                $assoc = [];

                foreach ($headers as $index => $header) {
                    if ($header === '') {
                        continue;
                    }

                    $assoc[$header] = $row[$index] ?? null;
                }

                return $assoc;
            })
            ->values()
            ->all();
    }

    protected function normalizeHeader(string $header): string
    {
        return str($header)
            ->lower()
            ->replace([' ', '-'], '_')
            ->value();
    }

    protected function normalizeRow(array $row, int $rowNumber): array
    {
        $code = trim((string) ($row['code'] ?? ''));
        $name = trim((string) ($row['name'] ?? ''));
        $type = strtolower(trim((string) ($row['type'] ?? '')));

        if ($code === '' || $name === '' || ! in_array($type, [Product::TYPE_CLOTHES, Product::TYPE_FABRIC], true)) {
            throw new InvalidArgumentException("Baris {$rowNumber} tidak valid. Kolom code, name, dan type wajib diisi dengan benar.");
        }

        $stocks = [
            'S' => $this->toInteger($row['stock_s'] ?? 0, $rowNumber, 'stock_s'),
            'M' => $this->toInteger($row['stock_m'] ?? 0, $rowNumber, 'stock_m'),
            'L' => $this->toInteger($row['stock_l'] ?? 0, $rowNumber, 'stock_l'),
            'XL' => $this->toInteger($row['stock_xl'] ?? 0, $rowNumber, 'stock_xl'),
            'XXL' => $this->toInteger($row['stock_xxl'] ?? 0, $rowNumber, 'stock_xxl'),
            'NONE' => $this->toInteger($row['stock_none'] ?? 0, $rowNumber, 'stock_none'),
        ];

        return [
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'description' => trim((string) ($row['description'] ?? '')) ?: null,
            'price' => $this->toFloat($row['price'] ?? 0, $rowNumber, 'price'),
            'best_seller' => $this->toBoolean($row['best_seller'] ?? false),
            'low_stock_threshold' => $this->toInteger($row['low_stock_threshold'] ?? 5, $rowNumber, 'low_stock_threshold'),
            'stocks' => $type === Product::TYPE_CLOTHES
                ? array_intersect_key($stocks, array_flip(Product::CLOTHING_SIZES))
                : ['NONE' => $stocks['NONE']],
        ];
    }

    protected function syncStockOpname(Product $product, array $stocks): void
    {
        $allowedSizes = $product->availableSizes();

        foreach ($allowedSizes as $size) {
            ProductStock::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'size' => $size,
                ],
                [
                    'stock' => (int) ($stocks[$size] ?? 0),
                ],
            );
        }

        ProductStock::query()
            ->where('product_id', $product->id)
            ->whereNotIn('size', $allowedSizes)
            ->delete();
    }

    protected function toInteger(mixed $value, int $rowNumber, string $column): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (! is_numeric($value)) {
            throw new InvalidArgumentException("Baris {$rowNumber} kolom {$column} harus berupa angka.");
        }

        return max(0, (int) $value);
    }

    protected function toFloat(mixed $value, int $rowNumber, string $column): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (! is_numeric($value)) {
            throw new InvalidArgumentException("Baris {$rowNumber} kolom {$column} harus berupa angka.");
        }

        return (float) $value;
    }

    protected function toBoolean(mixed $value): bool
    {
        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'ya', 'y'], true);
    }
}
