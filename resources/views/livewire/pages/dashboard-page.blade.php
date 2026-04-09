<div class="space-y-8 px-4 py-6 sm:px-6 lg:px-8">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="panel">
            <p class="text-sm text-slate-500">Total Produk</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $metrics['product_count'] }}</p>
        </div>
        <div class="panel">
            <p class="text-sm text-slate-500">Total Stok</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format($metrics['total_stock']) }}</p>
        </div>
        <div class="panel">
            <p class="text-sm text-slate-500">Stok Masuk Hari Ini</p>
            <p class="mt-3 text-3xl font-semibold text-sky-600">{{ number_format($metrics['stock_in_today']) }}</p>
        </div>
        <div class="panel">
            <p class="text-sm text-slate-500">Stok Keluar Hari Ini</p>
            <p class="mt-3 text-3xl font-semibold text-rose-600">{{ number_format($metrics['stock_out_today']) }}</p>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="panel">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Snapshot Stok Produk</h2>
                    <p class="text-sm text-slate-500">Ringkasan stok per ukuran untuk produk terbaru.</p>
                </div>
                <a wire:navigate href="{{ route('products') }}" class="btn btn-secondary">Kelola Produk</a>
            </div>

            <div class="space-y-4">
                @forelse ($stockSnapshot as $product)
                    <div class="rounded-2xl border border-slate-200/80 bg-white/70 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.24em] text-slate-400">{{ $product->code }}</p>
                                <h3 class="mt-1 text-base font-semibold text-slate-900">{{ $product->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">Tipe {{ ucfirst($product->type) }}</p>
                            </div>
                            <span class="badge">{{ ucfirst($product->type) }}</span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($product->stocks as $stock)
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm text-slate-600">
                                    {{ $stock->size }}: <strong>{{ $stock->stock }}</strong>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                        Belum ada produk. Tambahkan produk batik terlebih dahulu.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel">
                <h2 class="text-lg font-semibold text-slate-900">Stok Hampir Habis</h2>
                <p class="mt-1 text-sm text-slate-500">Produk yang sudah menyentuh threshold minimal.</p>

                <div class="mt-5 space-y-3">
                    @forelse ($lowStockProducts as $stock)
                        <div class="flex items-center justify-between rounded-2xl border border-amber-200/80 bg-amber-50/80 px-4 py-3">
                            <div>
                                <p class="font-medium text-slate-900">{{ $stock->product->name }}</p>
                                <p class="text-sm text-slate-500">{{ $stock->size }} • ambang {{ $stock->product->low_stock_threshold }}</p>
                            </div>
                            <span class="text-lg font-semibold text-amber-700">{{ $stock->stock }}</span>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-6 text-sm text-sky-700">
                            Semua stok masih dalam kondisi aman.
                        </p>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h2 class="text-lg font-semibold text-slate-900">Aktivitas Terbaru</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($latestActivities as $activity)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-medium text-slate-900">{{ $activity['product'] }}</p>
                                <span class="badge {{ $activity['type'] === 'Masuk' ? 'badge-success' : 'badge-danger' }}">{{ $activity['type'] }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">{{ $activity['size'] }} • Qty {{ $activity['quantity'] }} • {{ $activity['date']?->format('d M Y') }}</p>
                            @if ($activity['note'])
                                <p class="mt-1 text-sm text-slate-600">{{ $activity['note'] }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada transaksi stok.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
