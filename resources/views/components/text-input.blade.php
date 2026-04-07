@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full px-6 py-4 rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:border-sipega-navy focus:ring-4 focus:ring-sipega-navy/5 transition-all text-sm font-medium shadow-sm']) }}>
