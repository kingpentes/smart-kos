@props(['variant' => 'primary'])

@php
    $baseClasses = 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium tracking-wide border';
    
    $variants = [
        'primary' => 'bg-blue-50/80 text-blue-700 border-blue-100/50',
        'muted' => 'bg-slate-50 text-slate-600 border-slate-200/50',
        'success' => 'bg-emerald-50/80 text-emerald-700 border-emerald-100/50',
        'danger' => 'bg-rose-50/80 text-rose-700 border-rose-100/50',
        'warning' => 'bg-amber-50/80 text-amber-800 border-amber-100/50',
    ];
    
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
