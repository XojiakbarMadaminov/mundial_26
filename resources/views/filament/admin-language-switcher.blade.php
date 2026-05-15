@php
    $currentLocale = app()->getLocale();
@endphp

<div class="flex items-center gap-1 px-2 text-xs font-medium">
    <a
        href="{{ route('admin.locale', ['locale' => 'en']) }}"
        class="rounded-md px-2 py-1 {{ $currentLocale === 'en' ? 'bg-primary-600 text-white' : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5' }}"
    >
        EN
    </a>
    <a
        href="{{ route('admin.locale', ['locale' => 'uz']) }}"
        class="rounded-md px-2 py-1 {{ $currentLocale === 'uz' ? 'bg-primary-600 text-white' : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5' }}"
    >
        UZ
    </a>
</div>
