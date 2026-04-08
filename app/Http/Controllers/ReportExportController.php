<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Models\StockIn;
use App\Models\StockOut;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportExportController extends Controller
{
    public function stockSummary(): Response
    {
        $rows = ProductStock::query()
            ->with('product')
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->orderBy('products.name')
            ->orderBy('product_stocks.size')
            ->get(['product_stocks.*']);

        $content = $this->csv([
            ['Kode', 'Produk', 'Tipe', 'Ukuran', 'Stok Saat Ini'],
            ...$rows->map(fn (ProductStock $stock) => [
                $stock->product->code,
                $stock->product->name,
                $stock->product->type,
                $stock->size,
                $stock->stock,
            ])->all(),
        ]);

        return response($content, 200, $this->headers('laporan-stok.csv'));
    }

    public function transactions(Request $request, string $direction): Response
    {
        abort_unless(in_array($direction, ['in', 'out'], true), 404);

        $startDate = $request->string('start_date')->toString();
        $endDate = $request->string('end_date')->toString();

        $rows = $direction === 'in'
            ? StockIn::query()
                ->with('product')
                ->when($startDate !== '' && $endDate !== '', fn ($query) => $query->whereBetween('transaction_date', [$startDate, $endDate]))
                ->latest('transaction_date')
                ->latest()
                ->get()
            : StockOut::query()
                ->with('product')
                ->when($startDate !== '' && $endDate !== '', fn ($query) => $query->whereBetween('transaction_date', [$startDate, $endDate]))
                ->latest('transaction_date')
                ->latest()
                ->get();

        $header = $direction === 'in'
            ? ['Tanggal', 'Referensi', 'Kode', 'Produk', 'Ukuran', 'Qty', 'Catatan']
            : ['Tanggal', 'No Transaksi', 'Kode', 'Produk', 'Ukuran', 'Qty', 'Pembeli', 'Catatan'];

        $content = $this->csv([
            $header,
            ...$rows->map(function ($row) use ($direction) {
                if ($direction === 'in') {
                    return [
                        $row->transaction_date?->format('Y-m-d'),
                        $row->reference,
                        $row->product->code,
                        $row->product->name,
                        $row->size,
                        $row->quantity,
                        $row->note,
                    ];
                }

                return [
                    $row->transaction_date?->format('Y-m-d'),
                    $row->transaction_number,
                    $row->product->code,
                    $row->product->name,
                    $row->size,
                    $row->quantity,
                    $row->buyer_name,
                    $row->note,
                ];
            })->all(),
        ]);

        $filename = $direction === 'in' ? 'transaksi-stok-masuk.csv' : 'transaksi-stok-keluar.csv';

        return response($content, 200, $this->headers($filename));
    }

    protected function csv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }

    protected function headers(string $filename): array
    {
        return [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
    }
}
