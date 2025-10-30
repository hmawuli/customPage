<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'ensure.client.owns.page' => \App\Http\Middleware\EnsureClientOwnsPage::class,
    // Add other custom middleware here
    'ensure.email.verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
    ];
}
