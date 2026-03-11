<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pembatas akses route berdasarkan daftar role.
 */
class RoleMiddleware
{
    /**
    * Handle an incoming request.
    *
    * @param array<string> $roles
    */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        // Filter hanya role valid agar input typo tidak langsung meloloskan akses.
        $allowed = array_intersect($roles, Role::all());

        if (empty($allowed) || !$user->isRole(...$allowed)) {
            abort(403);
        }

        return $next($request);
    }
}
