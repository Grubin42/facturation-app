<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Journalisation pour le débogage
        Log::info('AdminMiddleware: Vérification de l\'utilisateur', [
            'user_authenticated' => $request->user() ? true : false,
            'user_id' => $request->user() ? $request->user()->id : null,
            'user_role' => $request->user() ? $request->user()->role : null
        ]);

        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$request->user()) {
            Log::error('AdminMiddleware: Utilisateur non connecté');
            return redirect()->route('login');
        }

        if (!$request->user()->role || $request->user()->role !== 'admin') {
            Log::error('AdminMiddleware: Utilisateur non administrateur', [
                'user_id' => $request->user()->id,
                'role' => $request->user()->role
            ]);
            abort(403, 'Accès non autorisé. Vous devez être administrateur pour accéder à cette page.');
        }

        return $next($request);
    }
}
