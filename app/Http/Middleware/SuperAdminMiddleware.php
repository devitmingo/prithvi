<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->role !== 'Superadmin') { // for API
            return response()->json(['message' => 'You do not have access to this resource.'], 403);
        }

        if (auth()->check() && auth()->user()->role !== 'Superadmin') { // for Web
            return to_route('login')->with('error', 'You do not have access to this resource.');
        }
        return $next($request);
    }
}
