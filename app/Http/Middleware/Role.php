<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Role
{
    /**
     * Middleware ról
     * Użycie: ->middleware('role:ADMIN,KIEROWNIK')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Brak autoryzacji');
        }

        $rola = strtoupper($user->rola ?? '');
        $roles = array_map('strtoupper', $roles);

        if (!in_array($rola, $roles, true)) {
            abort(403, 'Brak uprawnień do tej sekcji');
        }

        return $next($request);
    }
}
