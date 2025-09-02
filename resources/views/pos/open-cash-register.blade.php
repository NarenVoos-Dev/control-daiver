@extends('layouts.pos')

@section('title', 'Abrir Caja')
@section('page-title', 'Apertura de Caja')

@section('content')
<div class="flex items-center justify-center min-h-screen gradient-bg p-4">
    <div class="w-full max-w-lg animate-fade-in">
        <!-- Header Card -->
        <div class="glass-effect rounded-t-2xl p-6 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Apertura de Caja</h1>
            <p class="text-gray-600">Configura el monto inicial para comenzar las operaciones del día</p>

            <!-- Fecha y hora -->
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-center space-x-4 text-sm text-gray-600">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span id="currentDate"></span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock mr-2"></i>
                        <span id="currentTime"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="glass-effect rounded-b-2xl p-8 space-y-6">
            <form method="POST" action="{{ route('pos.open_cash_register.store') }}" class="space-y-6">
                @csrf
                <!-- Amount Input -->
                <div>
                    <label for="opening_balance" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-dollar-sign mr-2 text-green-600"></i>
                        Monto Inicial
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <span class="text-gray-500 text-lg font-semibold">$</span>
                        </div>
                        <input 
                            type="number" 
                            name="opening_balance" 
                            id="opening_balance" 
                            step="0.01" 
                            min="0"
                            required 
                            autofocus
                            placeholder="0.00"
                            class="input-focus w-full py-4 pl-10 pr-4 text-2xl font-semibold text-right border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-300"
                        >
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Ingresa el dinero en efectivo disponible al inicio del turno
                    </p>
                </div>

                <!-- Quick Amount Buttons -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                        Montos Rápidos
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <button type="button" onclick="setAmount(1000000)" class="quick-amount bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-700 py-3 px-4 rounded-lg font-medium">
                            $1.000.000
                        </button>
                        <button type="button" onclick="setAmount(500000)" class="quick-amount bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-700 py-3 px-4 rounded-lg font-medium">
                            $500.000
                        </button>
                        <button type="button" onclick="setAmount(100000)" class="quick-amount bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-700 py-3 px-4 rounded-lg font-medium">
                            $100.000
                        </button>
                    </div>
                </div>

                <!-- Cajero info (puedes traerlo de Auth::user()) -->
                <div class="bg-blue-50 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Cajero</p>
                            <p class="text-lg font-semibold text-blue-700">{{ Auth::user()->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-700">Turno</p>
                            <p class="text-lg font-semibold text-blue-700" id="shiftInfo"></p>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="space-y-3">
                    <button 
                        type="submit" 
                        class="btn-hover w-full py-4 px-6 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all duration-300"
                    >
                        <i class="fas fa-unlock-alt mr-2"></i>
                        Confirmar y Abrir Caja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
@push('scripts')
<script>
    // Actualizar fecha y hora
    function updateDateTime() {
        const now = new Date();
        document.getElementById('currentDate').textContent = now.toLocaleDateString('es-CO', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
        document.getElementById('currentTime').textContent = now.toLocaleTimeString('es-CO');
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();

    // Montos rápidos
    function setAmount(amount) {
        const input = document.getElementById('opening_balance');
        input.value = amount.toFixed(2);
        input.focus();
    }

    // Turnos por hora
    const hour = new Date().getHours();
    if (hour >= 6 && hour < 14) {
        document.getElementById('shiftInfo').textContent = 'Mañana';
    } else if (hour >= 14 && hour < 22) {
        document.getElementById('shiftInfo').textContent = 'Tarde';
    } else {
        document.getElementById('shiftInfo').textContent = 'Nocturno';
    }
</script>
@endpush
@endsection
