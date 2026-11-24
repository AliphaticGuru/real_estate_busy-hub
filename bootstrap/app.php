<?php

use App\Console\Commands\AppCreateAdminCommand;
use App\Console\Commands\PreLaunchCheckCommand;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Only the configured proxy addresses may set X-Forwarded-* headers.
        $proxies = collect(explode(',', (string) env('TRUSTED_PROXIES', '172.28.0.10')))
            ->map(static fn (string $proxy): string => trim($proxy))
            ->filter()
            ->values()
            ->all();

        $middleware->trustProxies(at: $proxies);
    })
    ->withCommands([
        AppCreateAdminCommand::class,
        PreLaunchCheckCommand::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
