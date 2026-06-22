@props(['messages' => null, 'field' => null])

@if ($field && $errors->has($field))
    <p {{ $attributes->merge(['class' => 'text-sm text-red-600 mt-1']) }}>
        {{ $errors->first($field) }}
    </p>
@elseif ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 mt-1 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@elseif ($slot->isNotEmpty())
    <p {{ $attributes->merge(['class' => 'text-sm text-red-600 mt-1']) }}>
        {{ $slot }}
    </p>
@endif
