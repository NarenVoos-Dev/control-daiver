@props([
    'type' => 'success',
    'title' => 'Éxito',
    'message' => 'La operación se completó correctamente.'
])

@php
    $colors = [
        'success' => [
            'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'iconColor' => 'text-green-600',
        ],
        'error' => [
            'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
            'iconColor' => 'text-red-600',
        ],
        'warning' => [
            'icon' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
            'iconColor' => 'text-yellow-600',
        ],
    ];
    $iconPath = $colors[$type]['icon'];
    $iconColor = $colors[$type]['iconColor'];
@endphp

<div role="alert" class="p-4 bg-white border border-gray-300 rounded-md shadow-sm">
  <div class="flex items-start gap-4">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 {{ $iconColor }}">
      <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
    </svg>

    <div class="flex-1">
      <strong class="font-medium text-gray-900">{{ $title }}</strong>
      <p class="mt-0.5 text-sm text-gray-700">{{ $message }}</p>
    </div>

    <button class="dismiss-alert -m-3 rounded-full p-1.5 text-gray-500 transition-colors hover:bg-gray-50 hover:text-gray-700" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
</div>