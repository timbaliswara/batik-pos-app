<div class="space-y-8 px-4 py-6 sm:px-6 lg:px-8">
    @if ($previewImage)
        <div wire:click="closePreview" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/80 px-4 py-8 backdrop-blur-sm">
            <div wire:click.stop class="relative w-full max-w-4xl">
                <button
                    wire:click="closePreview"
                    type="button"
                    class="absolute right-3 top-3 z-10 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-950/70 text-white shadow-lg transition hover:bg-slate-950"
                    aria-label="Tutup preview gambar"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>

                <div class="overflow-hidden rounded-[28px] border border-white/10 bg-white shadow-2xl">
                    <img src="{{ $previewImage }}" alt="{{ $previewName }}" class="max-h-[78vh] w-full object-contain bg-[#f5f5f7]" />
                </div>

                @if ($previewName)
                    <p class="mt-3 text-center text-sm font-medium text-white/90">{{ $previewName }}</p>
                @endif
            </div>
        </div>
    @endif

    @if ($pendingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 px-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-[28px] border border-white/70 bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-700">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-slate-950">Hapus Produk?</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Produk <span class="font-semibold text-slate-900">{{ $pendingDeleteName }}</span> akan dihapus permanen jika tidak memiliki histori transaksi dan stok aktif.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="cancelDelete" type="button" class="btn btn-secondary">Batal</button>
                    <button wire:click="delete" type="button" class="btn btn-danger">Ya, Hapus</button>
                </div>
            </div>
        </div>
    @endif

    <section class="hero-surface">
        <div class="relative z-10 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs uppercase tracking-[0.32em] text-slate-400">Catalog & Inventory</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Daftar Produk & Stok Batik</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-500">Tampilan dibuat lebih fokus untuk membaca produk, stok aktual, dan best seller tanpa distraksi berlebih. Cocok untuk pengecekan cepat dari desktop maupun mobile.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[28rem]">
                <div class="rounded-[24px] border border-white/80 bg-white/80 px-5 py-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Produk per halaman</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $products->count() }}</p>
                </div>
                <div class="rounded-[24px] border border-white/80 bg-white/80 px-5 py-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Urutan aktif</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-950">{{ $sortBy === 'best_seller' ? 'Best Seller + Abjad' : 'A-Z' }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="panel">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Filter Produk</h2>
                <p class="mt-1 text-sm text-slate-500">Cari cepat berdasarkan nama, kode, tipe, atau prioritaskan produk best seller.</p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau kode..." class="input min-w-64" />
                <select wire:model.live="filterType" class="input min-w-40">
                    <option value="all">Semua tipe</option>
                    <option value="baju">Baju</option>
                    <option value="kain">Kain</option>
                </select>
                <select wire:model.live="sortBy" class="input min-w-44">
                    <option value="alphabet">Urut Abjad A-Z</option>
                    <option value="best_seller">Best Seller + Abjad</option>
                </select>
                @if ($canManageInventory)
                <a href="{{ route('products.import') }}" class="btn btn-secondary whitespace-nowrap">Import Spreadsheet</a>
                <a href="{{ route('products.create') }}" class="btn btn-primary whitespace-nowrap">Tambah Produk</a>
                @endif
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if (! auth()->check())
        <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
            Halaman produk ini terbuka untuk umum sebagai katalog stok. Untuk mengelola best seller, edit, hapus, atau import data, silakan masuk dengan akun yang punya akses.
        </div>
    @elseif (! $canManageInventory)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Akun viewer hanya bisa melihat daftar produk dan stok. Penandaan best seller, edit, dan hapus hanya tersedia untuk Admin atau Kasir.
        </div>
    @endif

    <div class="panel">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Daftar Produk</h2>
                <p class="text-sm text-slate-500">{{ $products->count() }} produk dimuat pada halaman ini dengan urutan {{ $sortBy === 'best_seller' ? 'best seller lalu abjad' : 'abjad' }}.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 md:grid-cols-2 md:gap-4 xl:grid-cols-3 2xl:grid-cols-4">
            @forelse ($products as $product)
                <div @class([
                    'soft-card relative flex h-full flex-col overflow-hidden p-3 transition duration-200 sm:p-5',
                    'border-[#d6c3b4] bg-[linear-gradient(180deg,rgba(255,251,247,0.98),rgba(250,244,238,0.94))] shadow-[0_20px_55px_-34px_rgba(111,85,63,0.18)]' => $product->best_seller,
                ])>
                    <div class="flex flex-1 flex-col gap-3 sm:gap-4">
                        <div class="flex min-h-5 justify-end sm:min-h-6">
                            @if ($product->best_seller)
                                <div class="rounded-full bg-[#f3e6db] px-2 py-1 text-[0.52rem] font-semibold uppercase tracking-[0.14em] text-[#8b5e3c] sm:px-2.5 sm:text-[0.58rem] sm:tracking-[0.16em]">
                                    Best Seller
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:gap-4">
                            @if ($product->image)
                                <button
                                    type="button"
                                    wire:click="previewImage(@js(\Illuminate\Support\Facades\Storage::url($product->image)), @js($product->name))"
                                    class="block overflow-hidden rounded-[22px] text-left transition hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-slate-300 sm:rounded-3xl"
                                    aria-label="Perbesar gambar {{ $product->name }}"
                                >
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image) }}" alt="{{ $product->name }}" class="h-28 w-full object-cover shadow-[0_16px_30px_-24px_rgba(15,23,42,0.45)] sm:h-20 sm:w-20" />
                                </button>
                            @else
                                <div @class([
                                    'flex h-24 w-full flex-col items-center justify-center rounded-[22px] border text-center sm:h-20 sm:w-20 sm:rounded-3xl',
                                    'border-slate-200 bg-[linear-gradient(180deg,#f8fafc,#eef2f7)] text-slate-500' => ! $product->best_seller,
                                    'border-[#d8c2b3] bg-[linear-gradient(180deg,#fbf4ee,#f3e8df)] text-[#7a5a45]' => $product->best_seller,
                                ])>
                                    <svg class="h-5 w-5 opacity-70 sm:h-6 sm:w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 16.5V7.8A1.8 1.8 0 0 1 5.8 6h12.4A1.8 1.8 0 0 1 20 7.8v8.4M4 16.5l3.5-3.5a2 2 0 0 1 2.8 0l1.7 1.7m8-2.2-2-2a2 2 0 0 0-2.8 0l-3.2 3.2m-8 2.8h16M9 10h.01" />
                                    </svg>
                                    <span class="mt-1 text-[0.55rem] font-medium uppercase tracking-[0.16em] opacity-75 sm:text-[0.6rem] sm:tracking-[0.18em]">No Image</span>
                                </div>
                            @endif

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 @class([
                                        'line-clamp-2 text-sm font-semibold leading-5 sm:text-lg sm:leading-7',
                                        'text-slate-950' => ! $product->best_seller,
                                        'text-[#5d4638] tracking-[-0.01em]' => $product->best_seller,
                                    ])>{{ $product->name }}</h3>
                                    <span class="badge px-2 py-0.5 text-[0.65rem] sm:px-3 sm:py-1 sm:text-xs">{{ ucfirst($product->type) }}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 sm:text-sm">{{ $product->code }}</p>
                                <p class="mt-2 hidden text-sm text-slate-500 sm:block">{{ \Illuminate\Support\Str::limit($product->description, 120) }}</p>
                                <div @class([
                                    'mt-2 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium sm:mt-3 sm:px-3 sm:py-1.5 sm:text-sm',
                                    'bg-slate-100 text-slate-700' => ! $product->best_seller,
                                    'bg-[#efe2d6] text-[#7b573f]' => $product->best_seller,
                                ])>
                                    Total stok: {{ (int) $product->stocks_sum_stock }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto border-t border-slate-100/90 pt-3 sm:pt-4">
                            <p class="mb-2 text-xs font-medium text-slate-700 sm:mb-3 sm:text-sm">Stok per ukuran</p>
                            <div class="flex flex-wrap gap-1.5 sm:gap-2">
                                @foreach ($product->stocks as $stock)
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-[0.7rem] text-slate-700 sm:px-3 sm:py-1.5 sm:text-sm">
                                        {{ $stock->size }}: <strong>{{ $stock->stock }}</strong>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        @if ($canManageInventory)
                            <div class="border-t border-slate-100/90 pt-3 sm:hidden">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary w-full justify-center text-xs">Edit</a>
                            </div>

                            <div class="hidden border-t border-slate-100/90 pt-4 sm:flex sm:flex-row sm:flex-wrap sm:gap-2">
                                <button wire:click="toggleBestSeller({{ $product->id }})" type="button" class="btn btn-secondary w-full justify-center text-xs sm:w-auto sm:text-sm">
                                    {{ $product->best_seller ? 'Unmark Best Seller' : 'Mark Best Seller' }}
                                </button>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary w-full justify-center text-xs sm:w-auto sm:text-sm">Edit</a>
                                <button wire:click="confirmDelete({{ $product->id }})" type="button" class="btn btn-danger w-full justify-center text-xs sm:w-auto sm:text-sm">Hapus</button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-300 px-6 py-14 text-center text-slate-500">
                    Belum ada produk yang cocok dengan filter saat ini.
                </div>
            @endforelse
        </div>

        @if ($products->hasPages())
            <div class="mt-6 flex justify-center border-t border-slate-100/90 pt-5">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
