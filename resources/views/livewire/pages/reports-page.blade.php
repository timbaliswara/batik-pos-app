<div class="space-y-8 px-4 py-6 sm:px-6 lg:px-8">
    <section class="hero-surface">
        <div class="relative z-10 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs uppercase tracking-[0.32em] text-slate-400">Reporting</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Laporan Stok</h1>
                <p class="mt-3 text-sm leading-6 text-slate-500">Pantau ringkasan stok, arus masuk dan keluar, lalu ekspor laporan dengan tampilan yang lebih ringan dan mudah dibaca.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <div>
                    <label class="label">Mulai</label>
                    <input wire:model.live="start_date" type="date" class="input min-w-44" />
                </div>
                <div>
                    <label class="label">Selesai</label>
                    <input wire:model.live="end_date" type="date" class="input min-w-44" />
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="stat-card">
            <p class="text-sm text-slate-500">Total Stok Masuk</p>
            <p class="mt-3 text-3xl font-semibold text-sky-600">{{ number_format($summary['total_in']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-slate-500">Total Stok Keluar</p>
            <p class="mt-3 text-3xl font-semibold text-rose-600">{{ number_format($summary['total_out']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-slate-500">Baris Stok Aktif</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format($summary['stock_rows']) }}</p>
        </div>
    </section>

    <section class="panel">
        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Ringkasan Stok Saat Ini</h2>
                <p class="text-sm text-slate-500">Siap diunduh sebagai CSV yang bisa dibuka langsung di Excel.</p>
            </div>
            <a href="{{ route('reports.export.summary') }}" class="btn btn-secondary">Export Ringkasan</a>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Kode</th>
                            <th class="px-4 py-3 font-medium">Produk</th>
                            <th class="px-4 py-3 font-medium">Tipe</th>
                            <th class="px-4 py-3 font-medium">Ukuran</th>
                            <th class="px-4 py-3 font-medium">Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($stockSummary as $row)
                            <tr>
                                <td class="px-4 py-3">{{ $row->product->code }}</td>
                                <td class="px-4 py-3">{{ $row->product->name }}</td>
                                <td class="px-4 py-3">{{ ucfirst($row->product->type) }}</td>
                                <td class="px-4 py-3">{{ $row->size }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $row->stock }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="panel">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Transaksi Masuk</h2>
                    <p class="text-sm text-slate-500">{{ $start_date }} sampai {{ $end_date }}</p>
                </div>
                <a href="{{ route('reports.export.transactions', ['direction' => 'in', 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="btn btn-secondary">Export Masuk</a>
            </div>

            <div class="space-y-3">
                @forelse ($stockIns as $transaction)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-medium text-slate-900">{{ $transaction->product->name }}</p>
                            <span class="badge badge-success">{{ $transaction->quantity }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ $transaction->transaction_date?->format('d M Y') }} • {{ $transaction->size }} • {{ $transaction->reference ?: 'Tanpa referensi' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada transaksi masuk pada rentang ini.</p>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Transaksi Keluar</h2>
                    <p class="text-sm text-slate-500">{{ $start_date }} sampai {{ $end_date }}</p>
                </div>
                <a href="{{ route('reports.export.transactions', ['direction' => 'out', 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="btn btn-secondary">Export Keluar</a>
            </div>

            <div class="space-y-3">
                @forelse ($stockOuts as $transaction)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-medium text-slate-900">{{ $transaction->product->name }}</p>
                            <span class="badge badge-danger">{{ $transaction->quantity }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ $transaction->transaction_date?->format('d M Y') }} • {{ $transaction->size }} • {{ $transaction->buyer_name ?: 'Tanpa nama pembeli' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada transaksi keluar pada rentang ini.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
