<header class="relative flex items-center justify-between p-4 bg-white shadow">
    <h1 class="text-2xl font-bold">@yield('page-title', 'Punto de Venta')</h1>
    <div class="flex items-center space-x-4">
        <div class="font-semibold text-gray-700">{{ auth()->user()->business->name ?? 'Mi Negocio' }}</div>
        <button onclick="openExpenseModal()" class="flex items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Registrar Gasto
        </button>
        {{-- CAMBIO: Botón para la calculadora --}}
        <button id="calculator-toggle" class="p-2 text-gray-500 rounded-full hover:bg-gray-100">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M6.75 3h10.5A1.5 1.5 0 0 1 18.75 4.5v15A1.5 1.5 0 0 1 17.25 21H6.75A1.5 1.5 0 0 1 5.25 19.5v-15A1.5 1.5 0 0 1 6.75 3zm0 4.5h10.5m-6 3.75h.008v.008H11.25v-.008zm0 2.25h.008v.008H11.25v-.008zm0 2.25h.008v.008H11.25v-.008zm-2.25-4.5h.008v.008H9v-.008zm0 2.25h.008v.008H9v-.008zm0 2.25h.008v.008H9v-.008zm4.5-4.5h2.25m-2.25 2.25h2.25" />
            </svg>

        </button>
    </div>
    {{-- CAMBIO: Contenedor de la calculadora --}}
    @include('layouts.calculator')
</header>