<?php

namespace App\Livewire\Pages;

use App\Models\ProductStock;
use App\Models\StockIn;
use App\Models\StockOut;
use Livewire\Component;

class ReportsPage extends Component
{
    public string $start_date = '';

    public string $end_date = '';

    public function mount(): void
    {
        $this->start_date = today()->startOfMonth()->toDateString();
        $this->end_date = today()->toDateString();
    }

    public function render()
    {
        $stockSummary = ProductStock::query()
            ->with('product')
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->orderBy('products.name')
            ->orderBy('product_stocks.size')
            ->get(['product_stocks.*']);

        $stockIns = StockIn::query()
            ->with('product')
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
            ->latest('transaction_date')
            ->latest()
            ->get();

        $stockOuts = StockOut::query()
            ->with('product')
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
            ->latest('transaction_date')
            ->latest()
            ->get();

        $summary = [
            'total_in' => (int) $stockIns->sum('quantity'),
            'total_out' => (int) $stockOuts->sum('quantity'),
            'stock_rows' => $stockSummary->count(),
        ];

        return view('livewire.pages.reports-page', compact('stockSummary', 'stockIns', 'stockOuts', 'summary'));
    }
}
