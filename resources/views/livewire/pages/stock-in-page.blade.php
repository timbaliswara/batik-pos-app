<div class="space-y-8 px-4 py-6 sm:px-6 lg:px-8">
    <div>
        <h1 class="text-2xl font-semibold text-slate-950">Transaksi Stok Masuk</h1>
        <p class="mt-1 text-sm text-slate-500">Catat barang masuk berdasarkan varian batik dan ukuran.</p>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[0.88fr_1.12fr]">
        <div class="panel">
            <h2 class="text-lg font-semibold text-slate-900">Input Stok Masuk</h2>
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
                            <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                @foreach ($productResults as $product)
                                    <button wire:click="chooseProduct({{ $product->id }})" type="button" class="flex w-full items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 text-left text-sm transition hover:bg-slate-50 last:border-b-0">
                                        <span class="font-medium text-slate-800">{{ $product->name }}</span>
                                        <span class="text-slate-500">{{ $product->code }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        @error('product_id') <p class="error">{{ $message }}</p> @enderror
                    </div>

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
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-slate-900">Daftar yang Akan Disimpan</h3>
                            <p class="text-sm text-slate-500">{{ count($items) }} item siap diproses.</p>
                        </div>
                    </div>

                    @error('items') <p class="error mt-3">{{ $message }}</p> @enderror

                    <div class="mt-4 space-y-3">
                        @forelse ($items as $index => $item)
                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $item['product_name'] }}</p>
                                    <p class="text-sm text-slate-500">{{ $item['product_code'] }} • {{ $item['size'] }} • Qty {{ $item['quantity'] }}</p>
                                </div>
                                <button wire:click="removeItem({{ $index }})" type="button" class="btn btn-danger">Hapus</button>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada item yang ditambahkan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="label">Referensi</label>
                        <input wire:model="reference" type="text" class="input" />
                        @error('reference') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Tanggal</label>
                        <input wire:model="transaction_date" type="date" class="input" />
                        @error('transaction_date') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="label">Catatan</label>
                    <textarea wire:model="note" rows="4" class="input min-h-24"></textarea>
                    @error('note') <p class="error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full">Simpan Semua Stok Masuk</button>
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
                            <span class="badge badge-success">{{ $transaction->transaction_date?->format('d M Y') }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">Ref: {{ $transaction->reference ?: '-' }}</p>
                        @if ($transaction->note)
                            <p class="mt-1 text-sm text-slate-500">{{ $transaction->note }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada transaksi stok masuk.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
