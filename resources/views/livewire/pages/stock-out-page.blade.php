<div class="space-y-8 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <h1 class="text-2xl font-semibold text-slate-950">Transaksi Stok Keluar</h1>
        <p class="mt-1 text-sm text-slate-500">Gunakan untuk penjualan, pemakaian internal, atau pengurangan stok.</p>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[0.88fr_1.12fr]">
        <div class="panel">
            <h2 class="text-lg font-semibold text-slate-900">Input Stok Keluar</h2>
            <form wire:submit="save" class="mt-5 space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-slate-50/90 p-4">
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
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                            <p class="font-medium text-slate-800">Stok tersedia</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($selectedProduct->stocks as $stock)
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1">{{ $stock->size }}: {{ $stock->stock }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 grid gap-4 md:grid-cols-[0.9fr_0.7fr_auto]">
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
                            <label class="label">Jumlah</label>
                            <input wire:model="quantity" type="number" min="1" class="input" />
                            @error('quantity') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end">
                            <button wire:click="addItem" type="button" class="btn btn-secondary w-full justify-center md:w-auto">Tambah ke Daftar</button>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-4">
                    <div>
                        <h3 class="font-semibold text-slate-900">Daftar yang Akan Diproses</h3>
                        <p class="text-sm text-slate-500">{{ count($items) }} item siap diproses.</p>
                    </div>

                    @error('items') <p class="error mt-3">{{ $message }}</p> @enderror

                    <div class="mt-4 space-y-3">
                        @forelse ($items as $index => $item)
                            <div class="queue-item flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $item['product_name'] }}</p>
                                    <p class="text-sm text-slate-500">{{ $item['product_code'] }} • {{ $item['size'] }} • Qty {{ $item['quantity'] }}</p>
                                </div>
                                <button wire:click="removeItem({{ $index }})" type="button" class="btn btn-danger w-full justify-center sm:w-auto">Hapus</button>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada item yang ditambahkan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="label">No Transaksi</label>
                        <input wire:model="transaction_number" type="text" class="input" />
                        @error('transaction_number') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Nama Pembeli</label>
                        <input wire:model="buyer_name" type="text" class="input" />
                        @error('buyer_name') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="label">Tanggal</label>
                        <input wire:model="transaction_date" type="date" class="input" />
                        @error('transaction_date') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div></div>
                </div>

                <div>
                    <label class="label">Catatan</label>
                    <textarea wire:model="note" rows="4" class="input min-h-24"></textarea>
                    @error('note') <p class="error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full">Simpan Semua Stok Keluar</button>
            </form>
        </div>

        <div class="panel">
            <h2 class="text-lg font-semibold text-slate-900">Riwayat Terbaru</h2>
            <div class="mt-5 space-y-3">
                @forelse ($recentTransactions as $transaction)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $transaction->product->name }}</p>
                                <p class="text-sm text-slate-500">{{ $transaction->size }} • Qty {{ $transaction->quantity }}</p>
                            </div>
                            <span class="badge badge-danger">{{ $transaction->transaction_date?->format('d M Y') }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">No: {{ $transaction->transaction_number ?: '-' }} • Pembeli: {{ $transaction->buyer_name ?: '-' }}</p>
                        @if ($transaction->note)
                            <p class="mt-1 text-sm text-slate-500">{{ $transaction->note }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada transaksi stok keluar.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
