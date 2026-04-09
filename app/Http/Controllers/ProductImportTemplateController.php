<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class ProductImportTemplateController extends Controller
{
    public function __invoke(): Response
    {
        $rows = [
            ['code', 'name', 'type', 'description', 'best_seller', 'low_stock_threshold', 'stock_s', 'stock_m', 'stock_l', 'stock_xl', 'stock_xxl', 'stock_none'],
            ['BTK-PRM-010', 'Batik Parang Baru', 'baju', 'Kemeja batik premium', 'yes', '3', '5', '8', '7', '4', '2', '0'],
            ['KAIN-MOT-020', 'Kain Batik Motif Baru', 'kain', 'Kain meteran untuk stock opname', 'no', '10', '0', '0', '0', '0', '0', '25'],
        ];

        $handle = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle) ?: '';

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-import-produk.csv"',
        ]);
    }
}
