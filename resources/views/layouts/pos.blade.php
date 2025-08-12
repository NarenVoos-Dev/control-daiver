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

         /* Estilos para la calculadora */
        .calculator-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; }
        .calculator-btn {
            @apply w-full h-12 flex items-center justify-center text-lg font-semibold rounded-lg transition-colors;
        }
        .calculator-btn.num { @apply bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600; }
        .calculator-btn.op { @apply bg-blue-500 text-white hover:bg-blue-600; }
        .calculator-btn.eq { @apply bg-green-500 text-white hover:bg-green-600; }
        .calculator-btn.cl { @apply bg-red-500 text-white hover:bg-red-600; }
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
        {{-- CAMBIO: Script para la calculadora --}}
    <script>
        $(document).ready(function() {
            let display = $('#calculator-display');
            let currentInput = '';
            let operator = null;
            let firstValue = null;

            $('#calculator-toggle').on('click', function(e) {
                e.stopPropagation();
                $('#calculator-popover').toggleClass('hidden');
            });

            $('.calculator-btn').on('click', function() {
                const value = $(this).text();

                if ($(this).hasClass('num')) {
                    currentInput += value;
                    display.val(currentInput);
                } else if ($(this).hasClass('op')) {
                    if (currentInput) {
                        firstValue = parseFloat(currentInput);
                        currentInput = '';
                    }
                    operator = value;
                } else if ($(this).hasClass('eq')) {
                    if (firstValue !== null && operator && currentInput) {
                        const secondValue = parseFloat(currentInput);
                        let result;
                        switch (operator) {
                            case '+': result = firstValue + secondValue; break;
                            case '-': result = firstValue - secondValue; break;
                            case '*': result = firstValue * secondValue; break;
                            case '/': result = firstValue / secondValue; break;
                        }
                        display.val(result);
                        currentInput = result.toString();
                        firstValue = null;
                        operator = null;
                    }
                } else if ($(this).hasClass('cl')) {
                    currentInput = '';
                    operator = null;
                    firstValue = null;
                    display.val('');
                }
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#calculator-popover, #calculator-toggle').length) {
                    $('#calculator-popover').addClass('hidden');
                }
            });
        });
    </script>
</body>
</html>