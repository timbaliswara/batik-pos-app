<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Support\InventoryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductFormPage extends Component
{
    use WithFileUploads;

    public ?int $productId = null;

    public string $code = '';

    public string $name = '';

    public string $type = Product::TYPE_CLOTHES;

    public string $description = '';

    public string $price = '0';

    public string $low_stock_threshold = '5';

    public bool $best_seller = false;

    public ?string $existingImage = null;

    #[Validate('nullable|image|max:2048')]
    public $image;

    public function mount(?int $productId = null): void
    {
        abort_unless(auth()->user()?->canManageInventory(), 403);

        if (! $productId) {
            return;
        }

        $product = Product::query()->findOrFail($productId);

        $this->productId = $product->id;
        $this->code = $product->code;
        $this->name = $product->name;
        $this->type = $product->type;
        $this->description = $product->description ?? '';
        $this->price = (string) $product->price;
        $this->low_stock_threshold = (string) $product->low_stock_threshold;
        $this->best_seller = $product->best_seller;
        $this->existingImage = $product->image;
    }

    public function save(InventoryService $inventoryService): void
    {
        $validated = $this->validate($this->rules());

        $product = $this->productId ? Product::query()->findOrFail($this->productId) : new Product();
        $previousType = $product->type;

        if ($product->exists && $previousType !== $validated['type']) {
            $hasHistory = $product->stockIns()->exists() || $product->stockOuts()->exists();

            if ($hasHistory) {
                $this->addError('type', 'Tipe produk tidak bisa diubah setelah ada histori transaksi.');

                return;
            }
        }

        if ($this->image) {
            if ($this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }

            $validated['image'] = $this->image->store('products', 'public');
        } elseif ($this->existingImage) {
            $validated['image'] = $this->existingImage;
        }

        $product->fill($validated);
        $product->save();

        $inventoryService->syncProductStocks($product);

        session()->flash('status', $this->productId ? 'Produk berhasil diperbarui.' : 'Produk baru berhasil ditambahkan.');

        $this->redirectRoute('products', navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.product-form-page');
    }

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('products', 'code')->ignore($this->productId)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in([Product::TYPE_CLOTHES, Product::TYPE_FABRIC])],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'best_seller' => ['required', 'boolean'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
