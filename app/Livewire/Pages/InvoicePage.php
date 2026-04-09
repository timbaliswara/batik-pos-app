<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\StockOut;
use App\Support\InventoryService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use RuntimeException;

class InvoicePage extends Component
{
    public string $invoice_number = '';

    public string $invoice_date = '';

    public string $customer_name = '';

    public string $customer_phone = '';

    public string $customer_address = '';

    public string $note = '';

    public string $invoice_discount = '0';

    public string $product_id = '';

    public string $productSearch = '';

    public string $size = '';

    public string $quantity = '1';

    public string $unit_price = '0';

    public string $item_discount = '0';

    public array $items = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->canManageInventory(), 403);

        $this->resetInvoiceForm();
    }

    public function updatedProductId($value): void
    {
        $product = Product::query()->find($value);
        $this->size = $product?->availableSizes()[0] ?? '';
        $this->productSearch = $product ? $this->productLabel($product) : $this->productSearch;
    }

    public function updatedProductSearch(string $value): void
    {
        $selectedProduct = $this->product_id !== '' ? Product::query()->find($this->product_id) : null;
        $selectedLabel = $selectedProduct ? $this->productLabel($selectedProduct) : null;

        if ($selectedLabel !== $value) {
            $this->product_id = '';
            $this->size = '';
        }
    }

    public function chooseProduct(int $productId): void
    {
        $product = Product::query()->findOrFail($productId);

        $this->product_id = (string) $product->id;
        $this->productSearch = $this->productLabel($product);
        $this->size = $product->availableSizes()[0] ?? '';
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

        $quantity = (int) $validated['quantity'];
        $unitPrice = (float) $validated['unit_price'];
        $subtotal = $quantity * $unitPrice;
        $discount = min((float) ($validated['item_discount'] ?? 0), $subtotal);

        $this->items[] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'size' => $validated['size'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount' => $discount,
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal - $discount),
        ];

        $this->resetItemForm();
        $this->resetErrorBag(['product_id', 'size', 'quantity', 'unit_price', 'item_discount', 'items']);
    }

    public function removeItem(int $index): void
    {
        if (! array_key_exists($index, $this->items)) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function processInvoice(InventoryService $inventoryService)
    {
        $validated = $this->validate([
            'invoice_number' => ['required', 'string', 'max:100'],
            'invoice_date' => ['required', 'date'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:1000'],
            'invoice_discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (count($this->items) === 0) {
            $this->addError('items', 'Tambahkan minimal satu item ke daftar invoice.');

            return null;
        }

        if (StockOut::query()->where('transaction_number', $validated['invoice_number'])->exists()) {
            throw ValidationException::withMessages([
                'invoice_number' => 'Nomor invoice ini sudah pernah diproses. Gunakan nomor invoice yang berbeda.',
            ]);
        }

        try {
            $pdfPayload = DB::transaction(function () use ($inventoryService, $validated) {
                foreach ($this->items as $item) {
                    $product = Product::query()->with('stocks')->findOrFail($item['product_id']);
                    $availableStock = (int) optional($product->stocks->firstWhere('size', $item['size']))->stock;

                    if ($availableStock < (int) $item['quantity']) {
                        throw new RuntimeException("Stok {$product->name} ukuran {$item['size']} sudah tidak mencukupi.");
                    }

                    $inventoryService->recordStockOut($product, $item['size'], (int) $item['quantity'], [
                        'transaction_number' => $validated['invoice_number'],
                        'buyer_name' => $validated['customer_name'] ?: null,
                        'note' => $validated['note'] ?: null,
                        'transaction_date' => $validated['invoice_date'],
                    ]);
                }

                return $this->buildPdfPayload($validated);
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            $this->addError('items', $exception->getMessage());

            return null;
        }

        $token = (string) Str::uuid();
        Cache::put('invoice-download:'.$token, $pdfPayload, now()->addMinutes(10));

        $this->resetInvoiceForm();

        return redirect()->route('invoice.download', ['token' => $token]);
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

        return view('livewire.pages.invoice-page', [
            'subtotal' => $this->subtotal(),
            'itemDiscountTotal' => $this->itemDiscountTotal(),
            'grandTotal' => $this->grandTotal(),
            'selectedProduct' => $selectedProduct,
            'productResults' => $productResults,
        ]);
    }

    protected function itemRules(): array
    {
        $product = $this->product_id ? Product::query()->find($this->product_id) : null;
        $sizes = $product?->availableSizes() ?? Product::CLOTHING_SIZES;

        return [
            'product_id' => ['required', 'exists:products,id'],
            'size' => ['required', Rule::in($sizes)],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'item_discount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function queuedQuantity(int $productId, string $size): int
    {
        return collect($this->items)
            ->where('product_id', $productId)
            ->where('size', $size)
            ->sum('quantity');
    }

    protected function buildPdfPayload(array $validated): array
    {
        $subtotal = (float) collect($this->items)->sum('subtotal');
        $itemDiscountTotal = (float) collect($this->items)->sum('discount');
        $invoiceDiscount = min((float) ($validated['invoice_discount'] ?? 0), max(0, $subtotal - $itemDiscountTotal));
        $grandTotal = max(0, $subtotal - $itemDiscountTotal - $invoiceDiscount);

        return [
            'invoice_number' => $validated['invoice_number'],
            'invoice_date' => Carbon::parse($validated['invoice_date'])->toDateString(),
            'customer_name' => $validated['customer_name'] ?: null,
            'customer_phone' => $validated['customer_phone'] ?: null,
            'customer_address' => $validated['customer_address'] ?: null,
            'note' => $validated['note'] ?: null,
            'items' => $this->items,
            'subtotal' => $subtotal,
            'item_discount_total' => $itemDiscountTotal,
            'invoice_discount' => $invoiceDiscount,
            'grand_total' => $grandTotal,
            'logo_data_uri' => $this->logoDataUri(),
        ];
    }

    protected function subtotal(): float
    {
        return (float) collect($this->items)->sum('subtotal');
    }

    protected function itemDiscountTotal(): float
    {
        return (float) collect($this->items)->sum('discount');
    }

    protected function grandTotal(): float
    {
        $subtotal = $this->subtotal();
        $itemDiscountTotal = $this->itemDiscountTotal();
        $invoiceDiscount = min(max(0, (float) $this->invoice_discount), max(0, $subtotal - $itemDiscountTotal));

        return max(0, $subtotal - $itemDiscountTotal - $invoiceDiscount);
    }

    protected function resetInvoiceForm(): void
    {
        $this->invoice_date = today()->toDateString();
        $this->invoice_number = 'INV-'.now()->format('Ymd-His');
        $this->customer_name = '';
        $this->customer_phone = '';
        $this->customer_address = '';
        $this->note = '';
        $this->invoice_discount = '0';
        $this->items = [];
        $this->resetItemForm();
        $this->resetErrorBag();
    }

    protected function resetItemForm(): void
    {
        $this->product_id = '';
        $this->productSearch = '';
        $this->size = '';
        $this->quantity = '1';
        $this->unit_price = '0';
        $this->item_discount = '0';
    }

    protected function productLabel(Product $product): string
    {
        return $product->code.' - '.$product->name;
    }

    protected function logoDataUri(): ?string
    {
        $path = public_path('images/baliswara-logo.jpg');

        if (! file_exists($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';
        $data = base64_encode((string) file_get_contents($path));

        return "data:{$mime};base64,{$data}";
    }
}
