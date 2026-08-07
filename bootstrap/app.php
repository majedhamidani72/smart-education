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

        web: __DIR__.'/../routes/web.php',

        api: __DIR__.'/../routes/api.php',

        commands: __DIR__.'/../routes/console.php',

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

            return ApiResponse::error(

                'Internal Server Error',

                null,

                500

            );

        });

    })

    ->create();
