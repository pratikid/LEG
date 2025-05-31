@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700']) }}>
    {{ $value ?? (isset($slot) && !$slot->isEmpty() ? $slot : 'Label') }}
</label> 