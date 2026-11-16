<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperadminOrAgentOrSubAgentAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedRoles = ['Superadmin', 'Agent', 'Sub Agent'];

        if($request->user() && $request->user()->status == 3){
            return response()->json(['message' => 'Access denied. Your account has been deactivated'], 500);
        }

        if ($request->user() && !in_array($request->user()->role, $allowedRoles)) {
            return response()->json(['message' => 'You do not have access to this resource.'], 403);
        }

        if (auth()->check() && !in_array(auth()->user()->role, $allowedRoles)) {
            return to_route('login')->with('error', 'You do not have access to this resource.');
        }

        return $next($request);
    }
}
