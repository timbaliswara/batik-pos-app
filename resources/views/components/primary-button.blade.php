<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-full border border-transparent bg-[#1d1d1f] px-5 py-3 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-[#2b2b2f] focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 active:bg-black']) }}>
    {{ $slot }}
</button>
