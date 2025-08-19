
<x-guest-layout>
    <div class="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-700">
        <div class="flex w-full max-w-4xl mx-auto overflow-hidden bg-white rounded-lg shadow-lg dark:bg-gray-100">
            {{-- Columna Izquierda: Branding --}}
            <div class="hidden bg-cover md:block md:w-1/2" style="background-image: url('https://images.unsplash.com/photo-1578575437130-5278ce682728?q=80&w=2070&auto=format&fit=crop');">
                <div class="flex items-center h-full px-20 bg-gray-900 bg-opacity-40">
                    <div>
                        <h2 class="text-4xl font-bold text-black">Ges-Control</h2>
                        <p class="max-w-xl mt-3 font-semibold text-gray-900">
                            La herramienta definitiva para gestionar tu inventario, ventas y licencias. Todo en un solo lugar.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Columna Derecha: Formulario de Login --}}
            <div class="w-full px-6 py-8 md:w-1/2">
                <div class="flex justify-center mx-auto">
                    <img class="w-auto h-12" src="{{ asset('img/logo.svg') }}" alt="Logo">
                </div>

                <p class="mt-3 text-xl text-center text-gray-600 dark:text-black-500">
                    ¡Bienvenido de nuevo!
                </p>

                <x-validation-errors class="mb-4" />

                @session('status')
                    <div class="mb-4 text-sm font-medium text-green-600">
                        {{ $value }}
                    </div>
                @endsession

                <form method="POST" action="{{ route('login') }}" class="mt-8">
                    @csrf

                    <div>
                        <x-label for="email" value="{{ __('Correo Electrónico') }}" class="text-black-700 dark:text-black-200"/>
                        <x-input id="email" class="block w-full mt-2 " type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    </div>

                    <div class="mt-6">
                        <x-label for="password" value="{{ __('Contraseña') }}" class="text-black-700 dark:text-black-200"/>
                        <x-input id="password" class="block w-full mt-2" type="password" name="password" required autocomplete="current-password" />
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <label for="remember_me" class="flex items-center">
                            <x-checkbox id="remember_me" name="remember" />
                            <span class="text-sm text-gray-600 ms-2 dark:text-black-400">{{ __('Recordarme') }}</span>
                        </label>

                        <!--@if (Route::has('password.request'))
                            <a class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-500" href="{{ route('password.request') }}">
                                {{ __('¿Olvidaste tu contraseña?') }}
                            </a>
                        @endif-->
                    </div>
                    
                    <div class="mt-8">
                        <button type="submit" class="w-full px-4 py-3 text-lg font-bold tracking-wide text-white uppercase transform rounded-lg focus:outline-none" style="background-color: #635bff; hover:background-color: #524ae6;">
                            Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>