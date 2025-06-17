@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['id' => $attributes->get('id', 'text-input-'.uniqid()), 'name' => $attributes->get('name', 'text-input-'.uniqid()), 'class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) !!}> 