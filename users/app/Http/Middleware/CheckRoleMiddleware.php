<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleMiddleware
{
    /**
 * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role  The role to check.
     * @return mixed     */

    public function handle($request, Closure $next, $role)
    {
        if (!Auth::check() || !$request->user()->hasRole($role)) {
            // Si l'utilisateur n'est pas connecté ou n'a pas le rôle requis
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
