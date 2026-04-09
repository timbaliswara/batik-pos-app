<x-app-layout>
    <livewire:pages.product-form-page :product-id="$productId ?? null" :key="'product-form-'.($productId ?? 'create')" />
</x-app-layout>
