<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsPage extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = 'all';

    public string $sortBy = 'best_seller';

    public int $perPage = 24;

    public ?int $pendingDeleteId = null;

    public ?string $pendingDeleteName = null;

    public function toggleBestSeller(int $id): void
    {
        abort_unless(auth()->user()?->canManageInventory(), 403);

        $product = Product::query()->findOrFail($id);
        $product->update([
            'best_seller' => ! $product->best_seller,
        ]);

        session()->flash('status', $product->best_seller ? 'Produk ditandai sebagai best seller.' : 'Tanda best seller dihapus.');
    }

    public function confirmDelete(int $id): void
    {
        abort_unless(auth()->user()?->canManageInventory(), 403);

        $product = Product::query()->findOrFail($id);

        $this->pendingDeleteId = $product->id;
        $this->pendingDeleteName = $product->name;
    }

    public function cancelDelete(): void
    {
        $this->pendingDeleteId = null;
        $this->pendingDeleteName = null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function delete(): void
    {
        abort_unless(auth()->user()?->canManageInventory(), 403);
        abort_if(! $this->pendingDeleteId, 404);

        $product = Product::query()->with('stocks')->findOrFail($this->pendingDeleteId);

        if ($product->stockIns()->exists() || $product->stockOuts()->exists()) {
            $this->cancelDelete();
            session()->flash('status', 'Produk dengan histori transaksi tidak dapat dihapus.');

            return;
        }

        if ($product->stocks->sum('stock') > 0) {
            $this->cancelDelete();
            session()->flash('status', 'Kosongkan stok produk terlebih dahulu sebelum menghapus.');

            return;
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        $this->cancelDelete();

        session()->flash('status', 'Produk berhasil dihapus.');
    }

    public function render()
    {
        $products = Product::query()
            ->with('stocks')
            ->withSum('stocks', 'stock')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($builder) {
                    $builder
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterType !== 'all', fn ($query) => $query->where('type', $this->filterType))
            ->when($this->sortBy === 'best_seller', function ($query) {
                $query->orderByDesc('best_seller')->orderBy('name');
            }, function ($query) {
                $query->orderBy('name');
            })
            ->simplePaginate($this->perPage);

        $canManageInventory = auth()->user()?->canManageInventory() ?? false;

        return view('livewire.pages.products-page', compact('products', 'canManageInventory'));
    }
}
