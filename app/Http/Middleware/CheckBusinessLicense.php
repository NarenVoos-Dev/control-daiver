<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;
use Filament\Notifications\Notification;

class CheckBusinessLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Función auxiliar para manejar el logout y la notificación de forma limpia.
        $handleLogout = function (string $message, string $panelId = 'admin') {
            auth()->logout();

            Notification::make()
                ->title('Acceso Denegado')
                ->body($message)
                ->danger()
                ->send(); // Esto flashea la notificación a la sesión para la siguiente página

            // Redirigir a la página de login del panel correspondiente
            return redirect()->route("filament.{$panelId}.auth.login");
        };

        if ($user->hasRole('super-admin')) {
            return $handleLogout('Super Admin debe usar el panel de Super Admin.');
        }

        if (!$user->business) {
            return $handleLogout('Usuario no asociado a un negocio.');
        }

        $business = $user->business;
        if (!$business->is_active || ($business->license_expires_at && $business->license_expires_at < now())) {
            return $handleLogout('Tu licencia ha expirado o ha sido desactivada.');
        }
        
        return $next($request);
    }
}