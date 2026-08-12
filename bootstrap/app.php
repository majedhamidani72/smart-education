<?php

use App\Helpers\ApiResponse;
use App\Exceptions\Auth\ExpiredOtpException;
use App\Exceptions\Auth\InvalidLoginTokenException;
use App\Exceptions\Auth\InvalidOtpException;
use App\Exceptions\Auth\OtpAlreadyUsedException;
use App\Exceptions\Auth\OtpAttemptsExceededException;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(
    basePath: dirname(__DIR__)
)

    ->withRouting(

        web: __DIR__ . '/../routes/web.php',

        api: __DIR__ . '/../routes/api.php',

        commands: __DIR__ . '/../routes/console.php',

        health: '/up',

    )

    ->withMiddleware(function (
        Middleware $middleware
    ): void {

        //

    })

    ->withExceptions(function (
        Exceptions $exceptions
    ) {

        /*
        |--------------------------------------------------------------------------
        | Validation Exception
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (

            ValidationException $e,

            $request

        ) {

            if (! $request->expectsJson()) {

                return null;
            }

            return ApiResponse::validation(
                $e->errors()
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (

            AuthenticationException $e,

            $request

        ) {
            if (! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::unauthorized();
        });

        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (

            AccessDeniedHttpException $e,

            $request

        ) {
            if (! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::forbidden();
        });

        /*
        |--------------------------------------------------------------------------
        | Model Not Found
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (

            ModelNotFoundException $e,

            $request

        ) {
            if (! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::notFound(
                'Resource not found.'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | OTP Exceptions
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (

            InvalidLoginTokenException $e,

            $request

        ) {
            if (! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::error(

                $e->getMessage(),

                null,

                404

            );
        });

        $exceptions->render(function (

            InvalidOtpException $e,

            $request

        ) {
            if (! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::error(

                $e->getMessage(),

                null,

                422

            );
        });

        $exceptions->render(function (

            ExpiredOtpException $e,

            $request

        ) {
            if (! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::error(

                $e->getMessage(),

                null,

                410

            );
        });

        $exceptions->render(function (

            OtpAlreadyUsedException $e,

            $request

        ) {
            if (! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::error(

                $e->getMessage(),

                null,

                409

            );
        });

        $exceptions->render(function (

            OtpAttemptsExceededException $e,

            $request

        ) {
            if (! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::error(

                $e->getMessage(),

                null,

                429

            );
        });

        /*
        |--------------------------------------------------------------------------
        | Internal Server Error
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (

            Throwable $e,

            $request

        ) {
            if (! $request->expectsJson()) {
                return null;
            }

            return ApiResponse::error(

                app()->hasDebugModeEnabled()
                    ? $e->getMessage()
                    : 'Internal Server Error',

                null,

                500

            );
        });
    })

    ->create();
