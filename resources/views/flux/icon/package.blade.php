@props([
    'class' => '',
    'color' => null,
])

@php
    $colorClasses = match ($color) {
        'primary' => 'fill-primary-500 dark:fill-primary-400',
        'success' => 'fill-success-500 dark:fill-success-400',
        'warning' => 'fill-warning-500 dark:fill-warning-400',
        'error' => 'fill-error-500 dark:fill-error-400',
        'indigo' => 'fill-indigo-500 dark:fill-indigo-400',
        'rose' => 'fill-rose-500 dark:fill-rose-400',
        'pink' => 'fill-pink-500 dark:fill-pink-400',
        'purple' => 'fill-purple-500 dark:fill-purple-400',
        'blue' => 'fill-blue-500 dark:fill-blue-400',
        'zinc' => 'fill-zinc-500 dark:fill-zinc-400',
        'neutral' => 'fill-neutral-500 dark:fill-neutral-400',
        'amber' => 'fill-amber-500 dark:fill-amber-400',
        'lime' => 'fill-lime-500 dark:fill-lime-400',
        'emerald' => 'fill-emerald-500 dark:fill-emerald-400',
        'teal' => 'fill-teal-500 dark:fill-teal-400',
        'cyan' => 'fill-cyan-500 dark:fill-cyan-400',
        'sky' => 'fill-sky-500 dark:fill-sky-400',
        'gray' => 'fill-gray-500 dark:fill-gray-400',
        'stone' => 'fill-stone-500 dark:fill-stone-400',
        default => 'fill-current',
    };
@endphp

<svg 
    {{ $attributes->merge(['class' => 'size-[1.125rem] ' . $class . ' ' . $colorClasses]) }}
    xmlns="http://www.w3.org/2000/svg" 
    width="24" 
    height="24" 
    viewBox="0 0 24 24" 
    fill="none" 
    stroke="currentColor" 
    stroke-width="2" 
    stroke-linecap="round" 
    stroke-linejoin="round"
>
    <path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" />
    <path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9" />
    <path d="M12 3v6" />
</svg> 