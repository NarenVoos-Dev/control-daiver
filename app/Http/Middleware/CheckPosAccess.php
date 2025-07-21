<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Notifications\Notification; // <-- Importar la clase de Notificación

class CheckPosAccess
{

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Función auxiliar para manejar el logout y la notificación de forma limpia.
        $handleLogout = function (string $message) {
            auth()->logout();

            Notification::make()
                ->title('Acceso Denegado')
                ->body($message)
                ->danger()
                ->send(); // Esto "flashea" la notificación a la sesión para la siguiente página

            // Redirigir a la página de login del panel de administración principal
            return redirect()->route('filament.admin.auth.login');
        };

        // 1. Verificar que el usuario tenga el rol correcto.
        if (!$user->hasAnyRole(['admin', 'vendedor'])) {
            return $handleLogout('No tienes permiso para acceder al Punto de Venta.');
        }

        // 2. Verificar que el negocio tenga la licencia del POS activada.
        if (!$user->business || !$user->business->has_pos_access) {
            return $handleLogout('El acceso al Punto de Venta no está activado para este negocio.');
        }

        // Si todas las verificaciones pasan, permite que la solicitud continúe a la página del POS.
        return $next($request);
    }
}
