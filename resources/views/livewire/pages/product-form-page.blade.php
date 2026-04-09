<div class="space-y-8 px-4 py-6 sm:px-6 lg:px-8">
    <section class="hero-surface">
        <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs uppercase tracking-[0.32em] text-slate-400">Product Studio</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $productId ? 'Edit Produk Batik' : 'Tambah Produk Baru' }}</h1>
                <p class="mt-3 text-sm leading-6 text-slate-500">Ruang kerja yang lebih fokus untuk mengatur detail produk, gambar, kategori, dan penanda best seller dengan tampilan yang lebih tenang.</p>
            </div>
            <a wire:navigate href="{{ route('products') }}" class="btn btn-secondary">Kembali ke Daftar Produk</a>
        </div>
    </section>

    <div class="panel mx-auto max-w-4xl">
        <form wire:submit="save" class="space-y-5">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="label">Kode Batik</label>
                    <input wire:model="code" type="text" class="input" />
                    @error('code') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Nama Batik</label>
                    <input wire:model="name" type="text" class="input" />
                    @error('name') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="label">Kategori</label>
                    <select wire:model="type" class="input">
                        <option value="baju">Baju Batik</option>
                        <option value="kain">Kain Batik</option>
                    </select>
                    @error('type') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Threshold Low Stock</label>
                    <input wire:model="low_stock_threshold" type="number" min="0" class="input" />
                    @error('low_stock_threshold') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="label">Gambar Produk</label>
                    <input wire:model="image" type="file" class="input file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white" />
                    @error('image') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-slate-50/90 p-5">
                <label class="flex items-start gap-3">
                    <input wire:model="best_seller" type="checkbox" class="mt-1 h-5 w-5 rounded border-slate-300 text-sky-500 focus:ring-sky-300" />
                    <div>
                        <p class="font-medium text-slate-900">Tandai sebagai Best Seller</p>
                        <p class="text-sm text-slate-500">Produk best seller bisa diurutkan lebih dulu di halaman daftar produk.</p>
                    </div>
                </label>
                @error('best_seller') <p class="error">{{ $message }}</p> @enderror
            </div>

            @if ($image)
                <img src="{{ $image->temporaryUrl() }}" alt="Preview produk" class="h-48 w-full rounded-3xl object-cover" />
            @elseif ($existingImage)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($existingImage) }}" alt="Preview produk" class="h-48 w-full rounded-3xl object-cover" />
            @endif

            <div>
                <label class="label">Deskripsi</label>
                <textarea wire:model="description" rows="5" class="input min-h-32"></textarea>
                @error('description') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a wire:navigate href="{{ route('products') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">{{ $productId ? 'Simpan Perubahan' : 'Tambah Produk' }}</button>
            </div>
        </form>
    </div>
</div>
