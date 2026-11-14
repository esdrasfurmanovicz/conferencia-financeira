@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-rule focus:border-ok focus:ring-ok/25']) }}>
