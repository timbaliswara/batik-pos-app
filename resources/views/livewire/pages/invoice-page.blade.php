@php
    $formatRupiah = fn ($amount) => 'Rp '.number_format((float) $amount, 0, ',', '.');
@endphp

<div
    x-data="{ sectionProduct: true, sectionCustomer: false, sectionSummary: true }"
    class="space-y-8 px-4 py-6 pb-28 sm:px-6 sm:pb-6 lg:px-8"
>
    <section class="hero-surface">
        <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs uppercase tracking-[0.32em] text-slate-400">Invoice Customer</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Buat Invoice Penjualan Langsung</h1>
                <p class="mt-3 text-sm leading-6 text-slate-500">Alur invoice sekarang mengikuti pola stok keluar agar lebih stabil: cari produk, tambah ke daftar, lalu proses invoice dan unduh PDF.</p>
            </div>
        </div>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <div class="panel">
            <h2 class="text-lg font-semibold text-slate-900">Input Invoice</h2>
            <form wire:submit="processInvoice" class="mt-5 space-y-4">
                <section class="rounded-3xl border border-slate-200 bg-slate-50/90 p-4">
                    <button
                        type="button"
                        @click="sectionProduct = ! sectionProduct"
                        class="flex w-full items-center justify-between text-left sm:hidden"
                    >
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Pilih Produk</h3>
                            <p class="mt-1 text-xs text-slate-500">Cari produk, atur qty, lalu masukkan ke daftar.</p>
                        </div>
                        <svg class="h-5 w-5 text-slate-400 transition" :class="{ 'rotate-180': sectionProduct }" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                        </svg>
                    </button>
                    <div class="hidden sm:block">
                        <h3 class="text-base font-semibold text-slate-900">Pilih Produk</h3>
                        <p class="mt-1 text-sm text-slate-500">Cari produk, pilih ukuran, lalu masukkan ke daftar invoice.</p>
                    </div>

                    <div x-show="sectionProduct" class="mt-4 space-y-4">
                        <div>
                            <label class="label">Pilih Produk</label>
                            <input wire:model.live.debounce.250ms="productSearch" type="text" class="input" placeholder="Ketik nama atau kode produk..." autocomplete="off" />
                            @if ($selectedProduct)
                                <p class="mt-2 text-sm text-slate-500">Produk terpilih: <span class="font-medium text-slate-800">{{ $selectedProduct->code }} - {{ $selectedProduct->name }}</span></p>
                            @elseif ($productSearch !== '' && $productResults->isEmpty())
                                <p class="mt-2 text-sm text-slate-500">Produk tidak ditemukan.</p>
                            @endif

                            @if ($productResults->isNotEmpty() && ! $selectedProduct)
                                <div class="search-result-list">
                                    @foreach ($productResults as $product)
                                        <button
                                            wire:mousedown.prevent="chooseProduct({{ $product->id }})"
                                            type="button"
                                            @disabled((int) ($product->stocks_sum_stock ?? 0) <= 0)
                                            class="search-result-button disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400 disabled:opacity-100"
                                        >
                                            <span>
                                                <span class="block font-medium text-slate-800">{{ $product->name }}</span>
                                                <span class="mt-1 block text-xs text-slate-500">Stok total: {{ (int) ($product->stocks_sum_stock ?? 0) }}</span>
                                            </span>
                                            <span class="text-slate-500">
                                                {{ $product->code }}
                                                @if ((int) ($product->stocks_sum_stock ?? 0) <= 0)
                                                    <span class="ml-2 rounded-full bg-slate-200 px-2 py-0.5 text-[0.65rem] font-medium text-slate-500">Stok 0</span>
                                                @endif
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            @error('product_id') <p class="error">{{ $message }}</p> @enderror
                        </div>

                        @if ($selectedProduct)
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                                <p class="font-medium text-slate-800">Stok tersedia</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($selectedProduct->stocks as $stock)
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1">{{ $stock->size }}: {{ $stock->stock }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="grid gap-4 lg:grid-cols-[0.75fr_0.55fr_0.8fr_0.8fr_0.8fr]">
                            <div>
                                <label class="label">Ukuran</label>
                                <select wire:model="size" class="input" @disabled(!$selectedProduct)>
                                    <option value="">Pilih ukuran</option>
                                    @foreach ($selectedProduct?->availableSizes() ?? [] as $availableSize)
                                        <option value="{{ $availableSize }}">{{ $availableSize }}</option>
                                    @endforeach
                                </select>
                                @error('size') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">Qty</label>
                                <input wire:model.blur="quantity" type="number" min="1" class="input" />
                                @error('quantity') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">Harga Satuan</label>
                                <input wire:model.blur="unit_price" type="number" min="0" step="0.01" class="input" />
                                @error('unit_price') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">Diskon Item</label>
                                <input wire:model.blur="item_discount" type="number" min="0" step="0.01" class="input" />
                                @error('item_discount') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex items-end">
                                <button wire:click="addItem" type="button" class="btn btn-secondary w-full justify-center">Tambah ke Daftar</button>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="rounded-3xl border border-slate-200 bg-white p-4">
                    <div>
                        <h3 class="font-semibold text-slate-900">Daftar Item Invoice</h3>
                        <p class="text-sm text-slate-500">{{ count($items) }} item siap diproses.</p>
                    </div>

                    @error('items') <p class="error mt-3">{{ $message }}</p> @enderror

                    <div class="mt-4 space-y-3">
                        @forelse ($items as $index => $item)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/90 px-4 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $item['product_name'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $item['product_code'] }} • {{ $item['size'] }} • Qty {{ $item['quantity'] }}</p>
                                        <div class="mt-2 flex flex-wrap gap-1.5 text-[0.7rem] text-slate-600 sm:text-xs">
                                            <span class="rounded-full bg-white px-2 py-1">Harga {{ $formatRupiah($item['unit_price']) }}</span>
                                            <span class="rounded-full bg-white px-2 py-1">Diskon {{ $formatRupiah($item['discount']) }}</span>
                                            <span class="rounded-full bg-slate-900 px-2 py-1 font-medium text-white">Total {{ $formatRupiah($item['total']) }}</span>
                                        </div>
                                    </div>
                                    <button wire:click="removeItem({{ $index }})" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-rose-200 bg-rose-50 text-rose-700 transition hover:bg-rose-100" aria-label="Hapus item">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6 6 18" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada item yang ditambahkan.</p>
                        @endforelse
                    </div>
                </div>

                <section class="rounded-3xl border border-slate-200 bg-white p-4">
                    <button
                        type="button"
                        @click="sectionCustomer = ! sectionCustomer"
                        class="flex w-full items-center justify-between text-left sm:hidden"
                    >
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Data Customer</h3>
                            <p class="mt-1 text-xs text-slate-500">Isi nomor invoice, customer, dan catatan.</p>
                        </div>
                        <svg class="h-5 w-5 text-slate-400 transition" :class="{ 'rotate-180': sectionCustomer }" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                        </svg>
                    </button>
                    <div class="hidden sm:block">
                        <h3 class="text-base font-semibold text-slate-900">Data Customer</h3>
                        <p class="mt-1 text-sm text-slate-500">Lengkapi informasi invoice dan customer sebelum diproses.</p>
                    </div>

                    <div x-show="sectionCustomer" class="mt-4 space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="label">No Invoice</label>
                                <input wire:model="invoice_number" type="text" class="input" />
                                @error('invoice_number') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">Tanggal Invoice</label>
                                <input wire:model="invoice_date" type="date" class="input" />
                                @error('invoice_date') <p class="error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="label">Nama Customer</label>
                                <input wire:model="customer_name" type="text" class="input" />
                                @error('customer_name') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">No WhatsApp / Telepon</label>
                                <input wire:model="customer_phone" type="text" class="input" />
                                @error('customer_phone') <p class="error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="label">Alamat Customer</label>
                            <textarea wire:model="customer_address" rows="3" class="input min-h-24"></textarea>
                            @error('customer_address') <p class="error">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="label">Diskon Invoice</label>
                                <input wire:model.blur="invoice_discount" type="number" min="0" step="0.01" class="input" />
                                @error('invoice_discount') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">Catatan</label>
                                <textarea wire:model="note" rows="3" class="input min-h-24"></textarea>
                                @error('note') <p class="error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-4 sm:hidden">
                    <button
                        type="button"
                        @click="sectionSummary = ! sectionSummary"
                        class="flex w-full items-center justify-between text-left"
                    >
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Ringkasan</h3>
                            <p class="mt-1 text-xs text-slate-500">{{ count($items) }} item • Total {{ $formatRupiah($grandTotal) }}</p>
                        </div>
                        <svg class="h-5 w-5 text-slate-400 transition" :class="{ 'rotate-180': sectionSummary }" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div x-show="sectionSummary" class="mt-4 space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-medium text-slate-900">{{ $formatRupiah($subtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Diskon Item</span>
                            <span class="font-medium text-slate-900">{{ $formatRupiah($itemDiscountTotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Diskon Invoice</span>
                            <span class="font-medium text-slate-900">{{ $formatRupiah($invoice_discount) }}</span>
                        </div>
                        <div class="border-t border-slate-200 pt-3">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-900">Grand Total</span>
                                <span class="text-xl font-semibold text-slate-950">{{ $formatRupiah($grandTotal) }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <button type="submit" class="hidden w-full sm:inline-flex btn btn-primary">Proses Invoice & Download PDF</button>

                <div class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 px-4 py-3 shadow-[0_-18px_45px_-28px_rgba(15,23,42,0.28)] backdrop-blur sm:hidden">
                    <div class="mx-auto flex max-w-xl items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-400">Grand Total</p>
                            <p class="truncate text-lg font-semibold text-slate-950">{{ $formatRupiah($grandTotal) }}</p>
                        </div>
                        <button type="submit" class="btn btn-primary shrink-0 justify-center px-4">Proses</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="panel">
            <h2 class="text-lg font-semibold text-slate-900">Ringkasan Invoice</h2>
            <div class="mt-5 space-y-3">
                <div class="rounded-3xl border border-slate-200 bg-slate-50/90 p-4">
                    <p class="text-sm text-slate-500">No Invoice</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900">{{ $invoice_number ?: '-' }}</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-4">
                    <div class="flex items-center justify-between py-2 text-sm">
                        <span class="text-slate-500">Subtotal</span>
                        <span class="font-medium text-slate-900">{{ $formatRupiah($subtotal) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 text-sm">
                        <span class="text-slate-500">Diskon Item</span>
                        <span class="font-medium text-slate-900">{{ $formatRupiah($itemDiscountTotal) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 text-sm">
                        <span class="text-slate-500">Diskon Invoice</span>
                        <span class="font-medium text-slate-900">{{ $formatRupiah($invoice_discount) }}</span>
                    </div>
                    <div class="mt-3 border-t border-slate-200 pt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-semibold text-slate-900">Grand Total</span>
                            <span class="text-2xl font-semibold text-slate-950">{{ $formatRupiah($grandTotal) }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-900">
                    Invoice menggunakan pola yang sama seperti stok keluar, jadi pencarian produk dan penguncian stok `0` harusnya lebih stabil.
                </div>
            </div>
        </div>
    </div>
</div>
