<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLogContext
{
    /**
     * Attach stable request context to logs for easier incident tracing.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $route = $request->route();

        Log::withContext([
            'user_id' => $user?->id,
            'role' => $user?->role,
            'route' => $route?->getName() ?? $request->path(),
            'action' => $route?->getActionName(),
            'pengaduan_id' => $route?->parameter('pengaduan')?->id
                ?? $request->input('pengaduan_id'),
        ]);

        return $next($request);
    }
}
