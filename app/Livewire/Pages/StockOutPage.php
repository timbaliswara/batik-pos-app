<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\StockOut;
use App\Support\InventoryService;
use Illuminate\Validation\Rule;
use RuntimeException;
use Livewire\Component;

class StockOutPage extends Component
{
    public string $product_id = '';

    public string $productSearch = '';

    public string $size = '';

    public string $quantity = '1';

    public string $transaction_number = '';

    public string $buyer_name = '';

    public string $transaction_date = '';

    public string $note = '';

    public array $items = [];

    public function mount(): void
    {
        $this->transaction_date = today()->toDateString();
    }

    public function updatedProductId($value): void
    {
        $product = Product::query()->find($value);
        $this->size = $product?->availableSizes()[0] ?? '';
        $this->productSearch = $product ? $product->code.' - '.$product->name : $this->productSearch;
    }

    public function updatedProductSearch(string $value): void
    {
        $selectedProduct = $this->product_id !== '' ? Product::query()->find($this->product_id) : null;
        $selectedLabel = $selectedProduct ? $selectedProduct->code.' - '.$selectedProduct->name : null;

        if ($selectedLabel !== $value) {
            $this->product_id = '';
            $this->size = '';
        }
    }

    public function chooseProduct(int $productId): void
    {
        $product = Product::query()->findOrFail($productId);

        $this->product_id = (string) $product->id;
        $this->productSearch = $product->code.' - '.$product->name;
        $this->size = $product->availableSizes()[0] ?? '';
    }

    public function save(InventoryService $inventoryService): void
    {
        $validated = $this->validate([
            'transaction_number' => ['nullable', 'string', 'max:100'],
            'buyer_name' => ['nullable', 'string', 'max:100'],
            'transaction_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (count($this->items) === 0) {
            $this->addError('items', 'Tambahkan minimal satu item sebelum menyimpan.');

            return;
        }

        try {
            foreach ($this->items as $item) {
                $product = Product::query()->findOrFail($item['product_id']);

                $inventoryService->recordStockOut($product, $item['size'], (int) $item['quantity'], [
                    'transaction_number' => $validated['transaction_number'] ?: null,
                    'buyer_name' => $validated['buyer_name'] ?: null,
                    'note' => $validated['note'] ?: null,
                    'transaction_date' => $validated['transaction_date'],
                ]);
            }
        } catch (RuntimeException $exception) {
            $this->addError('items', $exception->getMessage());

            return;
        }

        $savedCount = count($this->items);

        $this->reset(['product_id', 'productSearch', 'size', 'quantity', 'transaction_number', 'buyer_name', 'note', 'items']);
        $this->quantity = '1';
        $this->transaction_date = today()->toDateString();

        session()->flash('status', $savedCount.' item stok keluar berhasil dicatat.');
    }

    public function addItem(): void
    {
        $validated = $this->validate($this->itemRules());
        $product = Product::query()->with('stocks')->findOrFail($validated['product_id']);
        $availableStock = (int) optional($product->stocks->firstWhere('size', $validated['size']))->stock;
        $queuedStock = $this->queuedQuantity($product->id, $validated['size']);

        if (($queuedStock + (int) $validated['quantity']) > $availableStock) {
            $this->addError('quantity', 'Jumlah melebihi stok yang tersedia untuk ukuran ini.');

            return;
        }

        $this->items[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'size' => $validated['size'],
            'quantity' => (int) $validated['quantity'],
        ];

        $this->reset(['product_id', 'productSearch', 'size', 'quantity']);
        $this->quantity = '1';
        $this->resetErrorBag(['product_id', 'size', 'quantity', 'items']);
    }

    public function removeItem(int $index): void
    {
        if (! array_key_exists($index, $this->items)) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function render()
    {
        $products = Product::query()->with('stocks')->orderBy('name')->get();
        $selectedProduct = $this->product_id ? $products->firstWhere('id', (int) $this->product_id) : null;
        $productResults = $this->productSearch === ''
            ? collect()
            : Product::query()
                ->select('id', 'code', 'name')
                ->withSum('stocks', 'stock')
                ->where(function ($query) {
                    $query
                        ->where('name', 'like', '%'.$this->productSearch.'%')
                        ->orWhere('code', 'like', '%'.$this->productSearch.'%');
                })
                ->orderBy('name')
                ->limit(8)
                ->get();
        $recentTransactions = StockOut::query()->with('product')->latest('transaction_date')->latest()->take(10)->get();

        return view('livewire.pages.stock-out-page', compact('products', 'selectedProduct', 'productResults', 'recentTransactions'));
    }

    protected function itemRules(): array
    {
        $product = $this->product_id ? Product::query()->find($this->product_id) : null;
        $sizes = $product?->availableSizes() ?? Product::CLOTHING_SIZES;

        return [
            'product_id' => ['required', 'exists:products,id'],
            'size' => ['required', Rule::in($sizes)],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    protected function queuedQuantity(int $productId, string $size): int
    {
        return collect($this->items)
            ->where('product_id', $productId)
            ->where('size', $size)
            ->sum('quantity');
    }
}
