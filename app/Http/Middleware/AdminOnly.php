<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminOnly
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if (!$user || strtoupper($user->rola ?? '') !== 'ADMIN') {
            abort(403, 'Tylko administrator ma dostęp.');
        }
        return $next($request);
    }
}
