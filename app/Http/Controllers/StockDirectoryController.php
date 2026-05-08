<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class StockDirectoryController extends Controller
{
    public function index(): View
    {
        return view('stock-directory', [
            'products' => $this->products(),
        ]);
    }

    public function pdf(): Response
    {
        $products = $this->products();

        $pdf = Pdf::loadView('pdf.stock-directory', [
            'products' => $products,
            'printedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('daftar-motif-dan-stok-baliswara.pdf');
    }

    protected function products()
    {
        return Product::query()
            ->with(['stocks' => fn ($query) => $query->orderBy('size')])
            ->withSum('stocks', 'stock')
            ->orderByDesc('best_seller')
            ->orderBy('name')
            ->get();
    }
}
