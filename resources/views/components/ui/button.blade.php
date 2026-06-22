@props(['variant' => 'primary', 'type' => 'submit', 'href' => null])

@php
    $baseClasses = 'inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer active:scale-[0.98]';
    
    $variants = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 hover:shadow-md hover:shadow-blue-500/20 focus:ring-blue-500',
        'secondary' => 'border border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus:ring-slate-200 shadow-sm',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 hover:shadow-md hover:shadow-rose-500/20 focus:ring-rose-500',
    ];
    
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
