
@php
    // Obtenemos el negocio del usuario autenticado
    $business = auth()->user()->business;
@endphp

{{-- Solo mostramos el nombre si el usuario tiene un negocio asignado --}}
@if ($business)
    <div class="px-4 py-2 font-semibold text-gray-700 text-m dark:text-gray-200">
        {{ $business->name }}
    </div>
@endif