<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedBlogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isApprovedBlogger()) {
            abort(403, 'Anda perlu mengajukan dan disetujui admin sebagai blogger terlebih dahulu.');
        }

        return $next($request);
    }
}
