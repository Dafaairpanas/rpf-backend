<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle request yang masuk.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user() || !$request->user()->role) {
            return ApiResponse::forbidden('Unauthorized Access');
        }

        if ($request->user()->role->name !== $role) {
            return ApiResponse::forbidden('You do not have permission to access this resource');
        }

        return $next($request);
    }
}
