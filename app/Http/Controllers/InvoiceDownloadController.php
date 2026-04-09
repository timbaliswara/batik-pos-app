<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class InvoiceDownloadController extends Controller
{
    public function __invoke(string $token)
    {
        $payload = Cache::pull('invoice-download:'.$token);

        abort_unless(is_array($payload), Response::HTTP_NOT_FOUND);

        $filename = Str::slug((string) ($payload['invoice_number'] ?? 'invoice')).'.pdf';

        return Pdf::loadView('pdf.invoice', $payload)
            ->setPaper('a4')
            ->download($filename !== '.pdf' ? $filename : 'invoice.pdf');
    }
}
