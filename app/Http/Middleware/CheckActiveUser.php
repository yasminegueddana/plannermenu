<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Vérifier si l'utilisateur est actif
            if (!$user->is_active) {
                Auth::logout();
                
                return redirect()->route('login')->with('error', 
                    'Votre compte a été désactivé par l\'administration. ' .
                    'Veuillez contacter l\'équipe support pour plus d\'informations.'
                );
            }
        }

        return $next($request);
    }
}
