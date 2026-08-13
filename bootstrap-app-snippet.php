<?php
// Tambahkan di bootstrap/app.php Anda, di dalam ->withMiddleware(function (Middleware $middleware) {...})

use App\Http\Middleware\EnsureApprovedBlogger;
use App\Http\Middleware\EnsureUserHasRole;

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => EnsureUserHasRole::class,
        'blogger.approved' => EnsureApprovedBlogger::class,
    ]);
})
