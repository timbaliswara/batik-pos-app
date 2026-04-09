<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-grid font-sans text-slate-900 antialiased">
        <div class="relative isolate min-h-screen overflow-hidden px-4 py-6 sm:px-6 lg:px-8">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-[radial-gradient(circle_at_top,rgba(186,168,154,0.28),transparent_58%)]"></div>
            <div class="relative mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-7xl items-stretch overflow-hidden rounded-[36px] border border-white/70 bg-white/55 shadow-[0_30px_100px_-55px_rgba(15,23,42,0.35)] backdrop-blur-xl">
                <section class="relative hidden w-full max-w-[46%] overflow-hidden bg-[linear-gradient(165deg,#5f524a_0%,#6f625a_38%,#f1f2f4_100%)] p-10 text-white lg:flex lg:flex-col lg:justify-between">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.22),transparent_34%),radial-gradient(circle_at_bottom_right,rgba(255,255,255,0.18),transparent_28%)]"></div>
                    <a href="/" class="relative inline-flex items-center gap-4 self-start">
                        <x-application-logo class="h-16 w-16 rounded-[1.5rem] shadow-[0_18px_45px_rgba(29,29,31,0.22)]" />
                        <div>
                            <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-white/70">Batik inventory</p>
                            <p class="mt-1 text-2xl font-semibold tracking-[0.08em]">Baliswara</p>
                        </div>
                    </a>

                    <div class="relative max-w-md space-y-6">
                        <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-medium uppercase tracking-[0.28em] text-white/80">
                            Inventory that feels refined
                        </span>
                        <div class="space-y-4">
                            <h1 class="text-4xl font-semibold leading-tight tracking-[-0.03em]">
                                Kelola stok batik dengan tampilan yang tenang, modern, dan elegan.
                            </h1>
                            <p class="max-w-sm text-sm leading-7 text-white/74">
                            Dalam menjalankan e-commerce, yang paling penting adalah tetap melakukan apa yang Anda lakukan saat ini dengan penuh semangat, untuk mempertahankannya.” – Jack Ma
                            </p>
                        </div>
                        <div class="grid gap-3 text-sm text-white/78">
                            <div class="rounded-3xl border border-white/15 bg-white/10 px-5 py-4 backdrop-blur">
                                Dashboard dan katalog disusun agar nyaman dipantau sepanjang hari.
                            </div>
                            <div class="rounded-3xl border border-white/15 bg-white/10 px-5 py-4 backdrop-blur">
                                Akses pengguna tetap aman, sementara pengalaman visual terasa premium.
                            </div>
                        </div>
                    </div>

                    <p class="relative text-xs uppercase tracking-[0.28em] text-white/55">
                        Designed for calm focus
                    </p>
                </section>

                <section class="relative flex w-full items-center justify-center bg-[linear-gradient(180deg,rgba(255,255,255,0.82),rgba(248,249,251,0.96))] px-5 py-10 sm:px-8 lg:max-w-[54%] lg:px-12">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-44 bg-[radial-gradient(circle_at_top,rgba(191,219,254,0.45),transparent_56%)]"></div>
                    <div class="relative w-full max-w-md">
                        <div class="mb-8 flex items-center gap-4 lg:hidden">
                            <a href="/" class="inline-flex items-center gap-3">
                                <x-application-logo class="h-14 w-14 rounded-[1.25rem] shadow-[0_18px_42px_rgba(29,29,31,0.16)]" />
                                <div>
                                    <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-slate-400">Batik inventory</p>
                                    <p class="mt-1 text-xl font-semibold tracking-[0.08em] text-slate-900">Baliswara</p>
                                </div>
                            </a>
                        </div>

                        {{ $slot }}
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>
