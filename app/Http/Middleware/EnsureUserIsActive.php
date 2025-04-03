<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('EnsureUserIsActive: middleware exécuté', [
            'path' => $request->path(),
            'method' => $request->method(),
            'is_authenticated' => Auth::check()
        ]);

        if (Auth::check()) {
            $user = Auth::user();

            Log::info('EnsureUserIsActive: Détails utilisateur', [
                'user_id' => $user->id,
                'email' => $user->email,
                'is_active_value' => $user->is_active,
                'is_active_type' => gettype($user->is_active)
            ]);

            if (!$user->is_active) {
                Log::warning('EnsureUserIsActive: Accès refusé pour utilisateur inactif', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'is_active_value' => $user->is_active
                ]);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('status', 'Votre compte n\'a pas encore été activé par un administrateur.');
            }
        }

        return $next($request);
    }
}
