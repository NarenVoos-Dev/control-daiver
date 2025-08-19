<!--<aside class="flex flex-col items-center w-16 text-gray-700 bg-white shadow-lg">
    <! Logo --
    <div class="flex items-center justify-center w-full h-16">
        <img src="{{ asset('img/favicon.svg') }}" alt="Logo" class="w-8 h-8">
    </div>
    <!- Links de Navegación --
    <nav class="flex flex-col items-center flex-grow mt-4 space-y-2">
        <a href="{{ route('pos.index') }}" class="flex items-center justify-center w-12 h-12 rounded-lg sidebar-link {{ request()->routeIs('pos.index') ? 'active' : '' }}" title="Punto de Venta">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5A.75.75 0 0 1 14.25 12h.5a.75.75 0 0 1 .75.75V21m-4.5 0v-7.5A.75.75 0 0 1 10.5 12h.5a.75.75 0 0 1 .75.75V21m-4.5 0V16.5a.75.75 0 0 1 .75-.75h.5a.75.75 0 0 1 .75.75V21m-4.5 0V18a.75.75 0 0 1 .75-.75h.5a.75.75 0 0 1 .75.75v3.75m-7.5 0V12A.75.75 0 0 1 3 11.25h.5A.75.75 0 0 1 4.25 12v9.75m-7.5 0h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm-3.75 0h.008v.008h-.008V8.25Zm-3.75 0h.008v.008h-.008V8.25Z" /></svg>
        </a>
        <a href="{{ route('pos.sales.list') }}" class="flex items-center justify-center w-12 h-12 rounded-lg sidebar-link {{ request()->routeIs('pos.sales.list') ? 'active' : '' }}" title="Ventas">
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
        </a>
    </nav>
    <!- Botón de Salir --
    <div class="flex items-center justify-center w-full h-16">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center justify-center w-12 h-12 text-gray-500 rounded-lg hover:bg-red-100 hover:text-red-500" title="Cerrar Sesión">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
            </button>
        </form>
    </div>
</aside>-->
<aside class="flex flex-col w-20 bg-gradient-to-b from-slate-900 to-slate-800 shadow-2xl border-r border-slate-700 transition-all duration-300 hover:w-64 group">
    <!-- Logo Section -->
    <div class="flex items-center justify-center h-20 border-b border-slate-700/50 relative overflow-hidden">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                <img src="{{ asset('img/favicon.svg') }}" alt="Logo" class="w-6 h-6">
            </div>
            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                <h1 class="text-white font-bold text-lg">Ges-Control</h1>
                <p class="text-slate-400 text-xs">Punto de Venta</p>
            </div>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex flex-col flex-grow mt-6 space-y-2 px-3">
        <a href="{{ route('pos.index') }}" class="sidebar-link {{ request()->routeIs('pos.index') ? 'active' : '' }}" title="Punto de Venta">
            <div class="flex items-center space-x-4 p-3 rounded-xl">
                <div class="w-6 h-6 flex-shrink-0 sidebar-icon text-gray-300"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5A.75.75 0 0 1 14.25 12h.5a.75.75 0 0 1 .75.75V21m-4.5 0v-7.5A.75.75 0 0 1 10.5 12h.5a.75.75 0 0 1 .75.75V21m-4.5 0V16.5a.75.75 0 0 1 .75-.75h.5a.75.75 0 0 1 .75.75V21m-4.5 0V18a.75.75 0 0 1 .75-.75h.5a.75.75 0 0 1 .75.75v3.75m-7.5 0V12A.75.75 0 0 1 3 11.25h.5A.75.75 0 0 1 4.25 12v9.75m-7.5 0h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm-3.75 0h.008v.008h-.008V8.25Zm-3.75 0h.008v.008h-.008V8.25Z"></path></svg></div>
                <span class="opacity-0 group-hover:opacity-100 text-gray-300 transition-opacity duration-300 whitespace-nowrap font-medium">Punto de Venta</span>
            </div>
            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-500 rounded-r-full opacity-0 active-indicator"></div>
        </a>
        <a href="{{ route('pos.sales.list') }}" class="sidebar-link {{ request()->routeIs('pos.sales.list') ? 'active' : '' }}" title="Ventas">
            <div class="flex items-center space-x-4 p-3 rounded-xl">
                <div class="w-6 h-6 flex-shrink-0 sidebar-icon text-gray-300"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"></path></svg></div>
                <span class="opacity-0 group-hover:opacity-100 text-gray-300 transition-opacity duration-300 whitespace-nowrap font-medium">Listado de Ventas</span>
            </div>
            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-500 rounded-r-full opacity-0 active-indicator"></div>
        </a>
        <a href="#" class="sidebar-link {{ request()->routeIs('pos.accounts.receivable') ? 'active' : '' }}" title="Cuentas por Cobrar">
            <div class="flex items-center space-x-4 p-3 rounded-xl">
                <div class="w-6 h-6 flex-shrink-0 sidebar-icon text-gray-300"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9H9m6 3H9m6 3H9m-3-9h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2z"></path></svg></div>
                <span class="opacity-0 group-hover:opacity-100 text-gray-300 transition-opacity duration-300 whitespace-nowrap font-medium">Cuentas por Cobrar</span>
            </div>
            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-500 rounded-r-full opacity-0 active-indicator"></div>
        </a>
    </nav>

    <!-- Logout Button -->
    <div class="border-t border-slate-700/50 p-3 mt-auto">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center space-x-4 p-3 rounded-xl text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-all duration-200" title="Cerrar Sesión">
                <div class="w-6 h-6 flex-shrink-0"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0-3-3m3 3H9"></path></svg></div>
                <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap font-medium">Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>