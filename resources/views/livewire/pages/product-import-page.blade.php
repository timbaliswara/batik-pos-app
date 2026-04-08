<div class="space-y-8 px-4 py-6 sm:px-6 lg:px-8">
    <section class="hero-surface">
        <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs uppercase tracking-[0.32em] text-slate-400">Spreadsheet Import</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Import Produk & Stock Opname</h1>
                <p class="mt-3 text-sm leading-6 text-slate-500">Unggah file spreadsheet untuk memperbarui katalog dan stok aktual dalam satu langkah. Alur ini cocok untuk stock opname manual yang dilakukan berkala.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('products.import.template') }}" class="btn btn-secondary">Unduh Template CSV</a>
                <a wire:navigate href="{{ route('products') }}" class="btn btn-secondary">Kembali ke Produk</a>
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
            <h2 class="text-lg font-semibold text-slate-900">Upload Spreadsheet</h2>
            <p class="mt-1 text-sm text-slate-500">Format yang didukung: `.xlsx`, `.xls`, dan `.csv`. File ini akan diperlakukan sebagai hasil stock opname terbaru.</p>

            <form wire:submit="save" class="mt-6 space-y-5">
                <div>
                    <label class="label">File Spreadsheet</label>
                    <input wire:model="spreadsheet" type="file" class="input file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white" />
                    @error('spreadsheet') <p class="error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn btn-primary">Proses Import</button>
            </form>
        </div>

        <div class="panel">
            <h2 class="text-lg font-semibold text-slate-900">Aturan Format</h2>
            <div class="mt-4 space-y-3 text-sm text-slate-600">
                <p><strong class="text-slate-900">Kolom wajib:</strong> `code`, `name`, `type`.</p>
                <p><strong class="text-slate-900">Tipe produk:</strong> gunakan `baju` atau `kain`.</p>
                <p><strong class="text-slate-900">Best seller:</strong> isi `yes/no`, `true/false`, atau `1/0`.</p>
                <p><strong class="text-slate-900">Stok baju:</strong> isi kolom `stock_s`, `stock_m`, `stock_l`, `stock_xl`, `stock_xxl`.</p>
                <p><strong class="text-slate-900">Stok kain:</strong> isi kolom `stock_none`.</p>
                <p><strong class="text-slate-900">Perilaku import:</strong> jika `code` sudah ada, data produk akan diperbarui dan stok akan diset ulang sesuai angka di spreadsheet.</p>
            </div>

            @if ($result)
                <div class="mt-6 rounded-3xl border border-sky-200 bg-sky-50/80 p-4">
                    <h3 class="font-semibold text-sky-800">Hasil Import</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl bg-white px-4 py-3 text-sm text-slate-700">
                            <p class="text-slate-500">Diproses</p>
                            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $result['processed'] }}</p>
                        </div>
                        <div class="rounded-2xl bg-white px-4 py-3 text-sm text-slate-700">
                            <p class="text-slate-500">Produk Baru</p>
                            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $result['created'] }}</p>
                        </div>
                        <div class="rounded-2xl bg-white px-4 py-3 text-sm text-slate-700">
                            <p class="text-slate-500">Diperbarui</p>
                            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $result['updated'] }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
