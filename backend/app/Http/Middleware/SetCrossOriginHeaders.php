<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCrossOriginHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Configurar headers para permitir Google OAuth y otras integraciones de terceros
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $response->headers->set('Cross-Origin-Embedder-Policy', 'unsafe-none');
        
        return $response;
    }
}
