<?php

use App\Http\Controllers\ProductImportTemplateController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\ProfileController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::view('/products', 'products')->name('products');

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/products/create', 'product-form')->middleware('role:admin,kasir')->name('products.create');
    Route::view('/products/import', 'product-import')->middleware('role:admin,kasir')->name('products.import');
    Route::get('/products/import/template', ProductImportTemplateController::class)->middleware('role:admin,kasir')->name('products.import.template');
    Route::get('/products/{product}/edit', function (Product $product) {
        return view('product-form', ['productId' => $product->id]);
    })->middleware('role:admin,kasir')->name('products.edit');
    Route::view('/reports', 'reports')->name('reports');

    Route::get('/reports/export/summary', [ReportExportController::class, 'stockSummary'])->name('reports.export.summary');
    Route::get('/reports/export/transactions/{direction}', [ReportExportController::class, 'transactions'])->name('reports.export.transactions');

    Route::middleware('role:admin,kasir')->group(function () {
        Route::view('/stock-in', 'stock-in')->name('stock-in');
        Route::view('/stock-out', 'stock-out')->name('stock-out');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
