<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HasHouse
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->house_id) {
            return redirect()->route('house.manager')->with('warning', 'Você precisa criar ou entrar em uma casa para aceder ao calendário.');
        }

        return $next($request);
    }
}
