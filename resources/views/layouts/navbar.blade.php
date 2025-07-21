<header class="flex items-center justify-between p-4 bg-white shadow">
    <h1 class="text-2xl font-bold">@yield('page-title', 'Punto de Venta')</h1>
    <div class="font-bold text-gray-900">
        {{ auth()->user()->business->name ?? 'Mi Negocio' }}
    </div>
</header>