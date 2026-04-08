<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockIn;
use App\Models\StockOut;
use Livewire\Component;

class DashboardPage extends Component
{
    public function render()
    {
        $stockQuery = ProductStock::query()->with('product');

        $metrics = [
            'product_count' => Product::query()->count(),
            'total_stock' => (int) $stockQuery->sum('stock'),
            'stock_in_today' => (int) StockIn::query()->whereDate('transaction_date', today())->sum('quantity'),
            'stock_out_today' => (int) StockOut::query()->whereDate('transaction_date', today())->sum('quantity'),
        ];

        $lowStockProducts = ProductStock::query()
            ->with('product')
            ->whereHas('product')
            ->get()
            ->filter(fn (ProductStock $stock) => $stock->stock <= $stock->product->low_stock_threshold)
            ->sortBy('stock')
            ->take(6)
            ->values();

        $latestActivities = collect()
            ->merge(
                StockIn::query()
                    ->with('product')
                    ->latest('transaction_date')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn (StockIn $row) => [
                        'type' => 'Masuk',
                        'product' => $row->product->name,
                        'size' => $row->size,
                        'quantity' => $row->quantity,
                        'date' => $row->transaction_date,
                        'note' => $row->reference ?: $row->note,
                    ])
            )
            ->merge(
                StockOut::query()
                    ->with('product')
                    ->latest('transaction_date')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn (StockOut $row) => [
                        'type' => 'Keluar',
                        'product' => $row->product->name,
                        'size' => $row->size,
                        'quantity' => $row->quantity,
                        'date' => $row->transaction_date,
                        'note' => $row->transaction_number ?: $row->buyer_name,
                    ])
            )
            ->sortByDesc(fn (array $row) => $row['date']?->timestamp ?? 0)
            ->take(8)
            ->values();

        $stockSnapshot = Product::query()
            ->with('stocks')
            ->latest()
            ->take(8)
            ->get();

        return view('livewire.pages.dashboard-page', compact('metrics', 'lowStockProducts', 'latestActivities', 'stockSnapshot'));
    }
}
