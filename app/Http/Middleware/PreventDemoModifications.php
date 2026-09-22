<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDemoModifications
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isDemo()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Acción deshabilitada en la versión de demostración del portafolio.',
                ], 403);
            }

            return back()->with('error', 'Acción deshabilitada en el modo demostración para preservar la integridad del portafolio.');
        }

        return $next($request);
    }
}
