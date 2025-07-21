<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($user && $user->hasRole('super-admin')) {
            return $next($request);
        }
        
        auth()->logout();
        throw new AuthenticationException('No autorizado para acceder a este panel.');
    }
}