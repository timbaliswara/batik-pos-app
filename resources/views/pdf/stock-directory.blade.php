<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Motif dan Stok Baliswara</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; }
        h1 { font-size: 20px; margin: 0 0 6px; }
        p { margin: 0 0 12px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 8px 6px; }
        th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; }
        td { vertical-align: top; }
        .center { text-align: center; }
        .best-seller { background: #fbbf24; color: #111827; font-weight: 700; }
        .row-best-seller td { background: #fef3c7; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h1>Daftar Motif dan Stok Baliswara</h1>
    <p>Dicetak {{ $printedAt->format('d M Y H:i') }}. Best seller ditandai untuk prioritas restock.</p>

    <table>
        <thead>
            <tr>
                <th>Prioritas</th>
                <th>Kode</th>
                <th>Nama Motif</th>
                <th>Tipe</th>
                <th>S</th>
                <th>M</th>
                <th>L</th>
                <th>XL</th>
                <th>XXL</th>
                <th>Kain</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                @php
                    $stocks = $product->stocks->pluck('stock', 'size');
                @endphp
                <tr class="{{ $product->best_seller ? 'row-best-seller' : '' }}">
                    <td class="center">
                        @if ($product->best_seller)
                            <span class="best-seller">BEST SELLER</span>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td>{{ $product->code }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ ucfirst($product->type) }}</td>
                    <td class="center">{{ $stocks->get('S', '-') }}</td>
                    <td class="center">{{ $stocks->get('M', '-') }}</td>
                    <td class="center">{{ $stocks->get('L', '-') }}</td>
                    <td class="center">{{ $stocks->get('XL', '-') }}</td>
                    <td class="center">{{ $stocks->get('XXL', '-') }}</td>
                    <td class="center">{{ $stocks->get('NONE', '-') }}</td>
                    <td class="center">{{ (int) $product->stocks_sum_stock }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="center muted">Belum ada produk yang bisa ditampilkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
