<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, $next, ...$guards)
    {
        // Appel de la méthode parent pour l'authentification de base
        $response = parent::handle($request, $next, ...$guards);

        // Si l'utilisateur est connecté mais pas actif, le déconnecter et rediriger
        if (Auth::check()) {
            $user = Auth::user();
            Log::info('Vérification utilisateur actif', [
                'user_id' => $user->id,
                'is_active' => $user->is_active ?? null
            ]);

            if (property_exists($user, 'is_active') && $user->is_active === false) {
                Log::warning('Tentative de connexion avec un compte inactif', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);

                // Déconnexion de l'utilisateur
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirection vers la page de connexion avec un message
                return redirect()->route('login')
                    ->with('status', 'Votre compte n\'est pas encore activé par un administrateur.');
            }
        }

        return $response;
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
