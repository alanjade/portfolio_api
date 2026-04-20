<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        /*
        |----------------------------------------------------------------------
        | Middleware Aliases
        |----------------------------------------------------------------------
        |
        | Laravel 11/12 no longer uses Kernel.php. Register all middleware
        | aliases here instead.
        |
        */
        $middleware->alias([
            // JWT authentication — replaces 'jwt.auth' from Kernel.php
            'jwt.auth'    => \App\Http\Middleware\JwtMiddleware::class,

            // JWT token refresh (optional, for sliding sessions)
            'jwt.refresh' => \Tymon\JWTAuth\Http\Middleware\RefreshToken::class,

            // Role guard — used if you later add role-based access
            'is_admin'    => \App\Http\Middleware\IsAdmin::class,

            'verified'    => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        /*
        |----------------------------------------------------------------------
        | CORS
        |----------------------------------------------------------------------
        |
        | HandleCors is already included globally by Laravel. Our cors.php
        | config drives its behaviour — no extra registration needed.
        |
        */

        /*
        |----------------------------------------------------------------------
        | JSON responses for unauthenticated API requests
        |----------------------------------------------------------------------
        |
        | Prevent Laravel from redirecting API 401s to a login HTML page.
        |
        */
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                // Return null to stop the redirect; the middleware will
                // fall through to the Unauthenticated exception handler.
                return null;
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Return JSON for unauthenticated API requests
        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            Request $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
        });

        // Return JSON 404 for API model-not-found
        $exceptions->render(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Resource not found'], 404);
            }
        });

        // Return JSON validation errors
        $exceptions->render(function (
            \Illuminate\Validation\ValidationException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });
    })
    ->create();
