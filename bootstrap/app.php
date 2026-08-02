<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Helpers\ApiResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

    // 404
    $exceptions->render(function (
        ModelNotFoundException $e,
        $request
    ) {
        return ApiResponse::notFound(
            'Resource not found.'
        );
    });

    // 422
    $exceptions->render(function (
        ValidationException $e,
        $request
    ) {
        return ApiResponse::validation(
            $e->errors()
        );
    });

    // 401
    $exceptions->render(function (
        AuthenticationException $e,
        $request
    ) {
        return ApiResponse::unauthorized();
    });

    // 403
    $exceptions->render(function (
        AccessDeniedHttpException $e,
        $request
    ) {
        return ApiResponse::forbidden();
    });

    // 500
    $exceptions->render(function (
        Throwable $e,
        $request
    ) {
        return ApiResponse::error(
            'Internal Server Error',
            null,
            500
        );
    });

})->create();
