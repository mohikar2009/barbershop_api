<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        if ($user->role->name !== $role) {
            return response()->json([
                'status' => false,
                'message' => 'شما اجازه دسترسی به این بخش را ندارید.'
            ], 403);
        }

        return $next($request);
    }
}
