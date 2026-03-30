<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserType
{
    public function handle(Request $request, Closure $next, string $type): Response
    {
        if ($request->user()?->type !== $type) {
            return $request->user()?->isCustomer()
                ? redirect()->route('customer.dashboard')
                : redirect()->route('dashboard');
        }

        return $next($request);
    }
}
