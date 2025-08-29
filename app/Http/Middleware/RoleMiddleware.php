<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Vérifier si l'utilisateur est actif
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Votre compte a été désactivé.');
        }

 // Vérifier si l'utilisateur a l'un des rôles requis
        if (!empty($roles) && !in_array($user->role, $roles)) {
            // Si l'utilisateur tente d'accéder au dashboard sans être admin
            if ($request->route()->named('dashboard')) {
                return redirect()->route('recipes.index');
            }
            
            // Pour les autres routes non autorisées
            abort(403, 'Accès non autorisé. Rôle requis : ' . implode(', ', $roles));
        }




        return $next($request);
    }
}
