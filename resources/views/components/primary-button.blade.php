<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-3 bg-sipega-orange border border-transparent rounded-full font-bold text-sm text-white uppercase tracking-widest hover:bg-orange-600 hover:-translate-y-0.5 hover:shadow-lg focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-sipega-orange focus:ring-offset-2 transition-all ease-in-out duration-200 shadow-md']) }}>
    {{ $slot }}
</button>
