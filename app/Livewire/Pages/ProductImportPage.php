<?php

namespace App\Livewire\Pages;

use App\Support\ProductSpreadsheetImportService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class ProductImportPage extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:xlsx,xls,csv|max:5120')]
    public $spreadsheet;

    public ?array $result = null;

    public function save(ProductSpreadsheetImportService $importService): void
    {
        abort_unless(auth()->user()?->canManageInventory(), 403);

        $this->validate();

        try {
            $path = $this->spreadsheet->store('imports', 'local');
            $fullPath = storage_path('app/private/'.$path);
            $this->result = $importService->import($fullPath);
            $this->reset('spreadsheet');
            session()->flash('status', 'Import produk dan stock opname berhasil diproses.');
        } catch (Throwable $exception) {
            $this->addError('spreadsheet', $exception->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pages.product-import-page');
    }
}
