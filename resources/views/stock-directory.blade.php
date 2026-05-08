<x-app-layout>
    <div class="space-y-8 px-4 py-6 sm:px-6 lg:px-8">
        <section class="hero-surface">
            <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs uppercase tracking-[0.32em] text-slate-400">Restock Directory</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Daftar Motif, Ukuran, dan Stok</h1>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Halaman ini dibuat khusus untuk merangkum motif dan stok yang siap dibagikan ke tim restock. Produk best seller ditandai lebih menonjol agar mudah diprioritaskan.
                    </p>
                </div>
                <a href="{{ route('stock-directory.pdf') }}" class="btn btn-primary w-full justify-center sm:w-auto">Export PDF</a>
            </div>
        </section>

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90">
                        <tr class="text-left text-xs uppercase tracking-[0.18em] text-slate-500">
                            <th class="px-4 py-3 font-semibold">Prioritas</th>
                            <th class="px-4 py-3 font-semibold">Kode</th>
                            <th class="px-4 py-3 font-semibold">Nama Motif</th>
                            <th class="px-4 py-3 font-semibold">Tipe</th>
                            <th class="px-4 py-3 font-semibold">S</th>
                            <th class="px-4 py-3 font-semibold">M</th>
                            <th class="px-4 py-3 font-semibold">L</th>
                            <th class="px-4 py-3 font-semibold">XL</th>
                            <th class="px-4 py-3 font-semibold">XXL</th>
                            <th class="px-4 py-3 font-semibold">Kain</th>
                            <th class="px-4 py-3 font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($products as $product)
                            @php
                                $stocks = $product->stocks->pluck('stock', 'size');
                            @endphp
                            <tr @class([
                                'align-top',
                                'bg-amber-50/90' => $product->best_seller,
                            ])>
                                <td class="px-4 py-3">
                                    @if ($product->best_seller)
                                        <span class="inline-flex rounded-full bg-amber-500 px-2.5 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.12em] text-white">
                                            Best Seller
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-700">{{ $product->code }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ ucfirst($product->type) }}</td>
                                <td class="px-4 py-3 text-center text-slate-700">{{ $stocks->get('S', '-') }}</td>
                                <td class="px-4 py-3 text-center text-slate-700">{{ $stocks->get('M', '-') }}</td>
                                <td class="px-4 py-3 text-center text-slate-700">{{ $stocks->get('L', '-') }}</td>
                                <td class="px-4 py-3 text-center text-slate-700">{{ $stocks->get('XL', '-') }}</td>
                                <td class="px-4 py-3 text-center text-slate-700">{{ $stocks->get('XXL', '-') }}</td>
                                <td class="px-4 py-3 text-center text-slate-700">{{ $stocks->get('NONE', '-') }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-slate-950">{{ (int) $product->stocks_sum_stock }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-10 text-center text-slate-500">
                                    Belum ada produk yang bisa ditampilkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
