<x-guest-layout>
    <div class="hero-surface p-0">
        <div class="relative overflow-hidden rounded-[32px] border border-white/80 bg-white/92 p-7 shadow-[0_28px_80px_-42px_rgba(15,23,42,0.18)] sm:p-8">
            <div class="mb-8 flex items-start justify-between gap-4">
                <div class="space-y-3">
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white/80 px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.26em] text-slate-500">
                        Secure access
                    </span>
                    <div class="space-y-2">
                        <h2 class="text-3xl font-semibold tracking-[-0.03em] text-slate-950">
                            Masuk ke dashboard Baliswara
                        </h2>
                        <p class="max-w-sm text-sm leading-6 text-slate-500">
                            Pantau produk, stok, dan pergerakan inventory dari satu ruang kerja yang lebih tenang dan rapi.
                        </p>
                    </div>
                </div>

                <div class="hidden rounded-[26px] border border-[#d8dadd] bg-[linear-gradient(180deg,#ffffff,#f6f7f9)] p-2 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.28)] sm:block">
                    <x-application-logo class="h-16 w-16 rounded-[1.4rem]" />
                </div>
            </div>

            <x-auth-session-status class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input
                        id="email"
                        class="mt-2 block w-full"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="nama@baliswara.com"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-4">
                        <x-input-label for="password" :value="__('Password')" class="mb-0" />
                        @if (Route::has('password.request'))
                            <a wire:navigate href="{{ route('password.request') }}" class="text-sm font-medium text-slate-500 transition hover:text-slate-900">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>

                    <x-text-input
                        id="password"
                        class="block w-full"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <label for="remember_me" class="flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 text-sm text-slate-600">
                    <input
                        id="remember_me"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-300"
                        name="remember"
                    >
                    <span>{{ __('Remember me on this device') }}</span>
                </label>

                <div class="space-y-3 pt-2">
                    <x-primary-button class="w-full justify-center rounded-2xl px-5 py-3 text-sm font-semibold normal-case tracking-normal">
                        {{ __('Log in') }}
                    </x-primary-button>

                    <p class="text-center text-xs leading-6 text-slate-400">
                        Akses hanya tersedia untuk admin dan pengguna yang sudah diberi izin.
                    </p>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
