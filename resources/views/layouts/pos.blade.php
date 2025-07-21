<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Punto de Venta')</title>
    <link rel="shortcut icon" href="{{ asset('img/favicon.svg') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-token" content="{{ $apiToken ?? '' }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        /* Estilo para el ítem activo del sidebar */
        .sidebar-link.active {
            background-color: #eef2ff; /* Color de fondo para el item activo */
            color: #4f46e5; /* Color de texto para el item activo */
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-300">
     {{-- Contenedor para las Alertas --}}
    <div id="alert-container" class="fixed z-50 w-full max-w-sm space-y-4 top-5 right-5"></div>
    
    <div class="flex h-screen bg-gray-300">
        <!-- Sidebar -->
         @include('layouts.sidebar')

        <!-- Contenido Principal -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Navbar -->
            @include('layouts.navbar')
            <!-- Área de Contenido de la Página -->
            <main class="flex-1 p-6 overflow-x-hidden overflow-y-auto bg-gray-250">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>