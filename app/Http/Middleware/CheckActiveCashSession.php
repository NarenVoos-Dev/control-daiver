<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CashSession;

class CheckActiveCashSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $activeSession = CashSession::where('business_id', auth()->user()->business_id)
                                    ->where('status', 'Abierta')
                                    ->first();

        // Si intentamos acceder a la página de "abrir caja"
        if ($request->routeIs('pos.open_cash_register.form')) {
            if ($activeSession) {
                return redirect()->route('pos.index');
            }
            return $next($request);
        }

        // Si intentamos acceder a cualquier otra página del POS (excepto la de cierre) y NO hay sesión
        if (!$activeSession && !$request->routeIs('pos.close_cash_register.*')) {
            return redirect()->route('pos.open_cash_register.form');
        }

        // Si intentamos acceder a la página de CIERRE y SÍ hay sesión
        if ($activeSession && $request->routeIs('pos.close_cash_register.*')) {
             $request->attributes->add(['active_session' => $activeSession]);
             return $next($request);
        }

        // Si hay una sesión activa, le inyectamos el ID para usarlo después
        if ($activeSession) {
            $request->attributes->add(['cash_session_id' => $activeSession->id]);
        }

        return $next($request);
    }
}