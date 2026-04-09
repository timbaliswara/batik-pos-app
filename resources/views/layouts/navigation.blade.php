@php
    $links = [
        ['label' => 'Produk', 'route' => 'products'],
    ];

    if (auth()->check()) {
        array_unshift($links, ['label' => 'Dashboard', 'route' => 'dashboard']);
        $links[] = ['label' => 'Laporan', 'route' => 'reports'];
    }

    if (auth()->user()?->canManageInventory()) {
        $links[] = ['label' => 'Invoice', 'route' => 'invoice'];
        $links[] = ['label' => 'Stok Masuk', 'route' => 'stock-in'];
        $links[] = ['label' => 'Stok Keluar', 'route' => 'stock-out'];
    }
@endphp

<nav x-data="{ open: false }" class="border-b border-white/70 bg-white/72 backdrop-blur-2xl">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-10">
            <a wire:navigate href="{{ auth()->check() ? route('dashboard') : route('products') }}" class="flex items-center gap-3">
                <x-application-logo class="h-10 w-10" />
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Stock Baliswara</p>
                    <p class="text-lg font-semibold text-slate-950">Monitoring</p>
                </div>
            </a>

            <div class="hidden items-center gap-2 lg:flex">
                @foreach ($links as $link)
                    <a wire:navigate href="{{ route($link['route']) }}" class="{{ request()->routeIs($link['route']) ? 'bg-[#1d1d1f] text-white shadow-[0_12px_30px_-18px_rgba(29,29,31,0.45)]' : 'text-slate-500 hover:bg-slate-100/90 hover:text-slate-900' }} rounded-full px-4 py-2 text-sm font-medium transition">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="hidden items-center gap-3 lg:flex">
            @auth
                <a wire:navigate href="{{ route('profile.edit') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:text-slate-900">
                    {{ Auth::user()->name }} • {{ ucfirst(Auth::user()->role) }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Keluar</button>
                </form>
            @else
                <a wire:navigate href="{{ route('login') }}" class="btn btn-secondary">Masuk Admin</a>
            @endauth
        </div>

        <button @click="open = ! open" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-600 lg:hidden">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
    </div>

    <div x-show="open" x-transition class="border-t border-slate-200/80 bg-white/95 px-4 py-4 lg:hidden">
        <div class="space-y-2">
            @foreach ($links as $link)
                <a wire:navigate href="{{ route($link['route']) }}" class="{{ request()->routeIs($link['route']) ? 'bg-[#1d1d1f] text-white' : 'bg-slate-50/90 text-slate-700' }} block rounded-2xl px-4 py-3 text-sm font-medium">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        <div class="mt-4 rounded-2xl border border-slate-200 bg-white/80 p-4">
            @auth
                <p class="font-medium text-slate-900">{{ Auth::user()->name }}</p>
                <p class="text-sm text-slate-500">{{ Auth::user()->email }}</p>
                <p class="mt-1 text-xs uppercase tracking-[0.22em] text-slate-400">{{ Auth::user()->role }}</p>
                <div class="mt-4 flex gap-2">
                    <a wire:navigate href="{{ route('profile.edit') }}" class="btn btn-secondary flex-1 justify-center">Profil</a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="btn btn-secondary w-full justify-center">Keluar</button>
                    </form>
                </div>
            @else
                <p class="font-medium text-slate-900">Akses Publik</p>
                <p class="text-sm text-slate-500">Anda bisa melihat daftar produk dan stok tanpa login.</p>
                <div class="mt-4">
                    <a wire:navigate href="{{ route('login') }}" class="btn btn-secondary w-full justify-center">Masuk Admin</a>
                </div>
            @endauth
        </div>
    </div>
</nav>
