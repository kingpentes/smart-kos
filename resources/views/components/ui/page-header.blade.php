@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
        @if($description)
            <p class="text-sm text-slate-500 mt-1">{{ $description }}</p>
        @endif
    </div>
    
    @if(isset($actions))
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
