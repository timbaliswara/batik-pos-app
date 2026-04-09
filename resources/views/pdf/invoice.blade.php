@php
    $formatRupiah = fn ($amount) => 'Rp '.number_format((float) $amount, 0, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        .page { padding: 28px 34px; }
        .header-table, .meta-table, .items-table, .totals-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .brand-title { font-size: 24px; font-weight: 700; color: #111827; margin: 0 0 4px; }
        .brand-subtitle { font-size: 11px; color: #6b7280; margin: 0; }
        .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1.6px; color: #9ca3af; margin-bottom: 8px; }
        .meta-card { border: 1px solid #e5e7eb; border-radius: 14px; padding: 14px; }
        .items-table th { background: #f8fafc; color: #475569; font-size: 11px; text-align: left; padding: 10px; border-bottom: 1px solid #e5e7eb; }
        .items-table td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        .totals-table td { padding: 6px 0; }
        .text-right { text-align: right; }
        .grand-total { font-size: 20px; font-weight: 700; color: #111827; }
        .muted { color: #6b7280; }
        .note-box { margin-top: 18px; border: 1px solid #e5e7eb; border-radius: 14px; padding: 14px; background: #fcfcfd; }
        .logo { width: 68px; height: 68px; object-fit: cover; border-radius: 16px; }
    </style>
</head>
<body>
    <div class="page">
        <table class="header-table">
            <tr>
                <td style="width: 76px;">
                    @if ($logo_data_uri)
                        <img src="{{ $logo_data_uri }}" alt="Baliswara" class="logo">
                    @endif
                </td>
                <td>
                    <p class="brand-title">Baliswara</p>
                    <p class="brand-subtitle">Invoice penjualan langsung untuk customer</p>
                </td>
                <td class="text-right">
                    <div class="section-title">Invoice</div>
                    <div style="font-size: 18px; font-weight: 700; color: #111827;">{{ $invoice_number }}</div>
                    <div class="muted" style="margin-top: 4px;">{{ $invoice_date->translatedFormat('d F Y') }}</div>
                </td>
            </tr>
        </table>

        <table class="meta-table" style="margin-top: 24px;">
            <tr>
                <td style="width: 58%; padding-right: 10px;">
                    <div class="meta-card">
                        <div class="section-title">Customer</div>
                        <div style="font-size: 15px; font-weight: 700; color: #111827;">{{ $customer_name ?: 'Customer' }}</div>
                        <div style="margin-top: 6px;" class="muted">{{ $customer_phone ?: '-' }}</div>
                        <div style="margin-top: 6px; line-height: 1.6;" class="muted">{{ $customer_address ?: '-' }}</div>
                    </div>
                </td>
                <td style="width: 42%; padding-left: 10px;">
                    <div class="meta-card">
                        <div class="section-title">Ringkasan</div>
                        <table class="totals-table">
                            <tr>
                                <td class="muted">Subtotal</td>
                                <td class="text-right">{{ $formatRupiah($subtotal) }}</td>
                            </tr>
                            <tr>
                                <td class="muted">Diskon Item</td>
                                <td class="text-right">{{ $formatRupiah($item_discount_total) }}</td>
                            </tr>
                            <tr>
                                <td class="muted">Diskon Invoice</td>
                                <td class="text-right">{{ $formatRupiah($invoice_discount) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="padding-top: 10px; border-top: 1px solid #e5e7eb;"></td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700; color: #111827;">Grand Total</td>
                                <td class="text-right grand-total">{{ $formatRupiah($grand_total) }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <table class="items-table" style="margin-top: 24px;">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 38%;">Item</th>
                    <th style="width: 12%;" class="text-right">Qty</th>
                    <th style="width: 17%;" class="text-right">Harga</th>
                    <th style="width: 13%;" class="text-right">Diskon</th>
                    <th style="width: 14%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div style="font-weight: 700; color: #111827;">{{ $item['name'] }}</div>
                            <div class="muted" style="margin-top: 3px;">{{ $item['code'] }} • Ukuran {{ $item['size'] }}</div>
                        </td>
                        <td class="text-right">{{ $item['quantity'] }}</td>
                        <td class="text-right">{{ $formatRupiah($item['unit_price']) }}</td>
                        <td class="text-right">{{ $formatRupiah($item['discount']) }}</td>
                        <td class="text-right">{{ $formatRupiah($item['total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="note-box">
            <div class="section-title">Catatan</div>
            <div style="line-height: 1.7;">{{ $note ?: 'Terima kasih telah berbelanja di Baliswara.' }}</div>
        </div>
    </div>
</body>
</html>
