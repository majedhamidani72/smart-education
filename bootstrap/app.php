<?php

use App\Helpers\ApiResponse;
use App\Exceptions\Auth\ExpiredOtpException;
use App\Exceptions\Auth\InvalidLoginTokenException;
use App\Exceptions\Auth\InvalidOtpException;
use App\Exceptions\Auth\OtpAlreadyUsedException;
use App\Exceptions\Auth\OtpAttemptsExceededException;
use App\Http\Middleware\EnsureAgreementAccepted;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
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
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | Trust Proxies
        |--------------------------------------------------------------------------
        | لیارا (و هر PaaS مشابه) بین مرورگر و کانتینر ما یک Reverse Proxy
        | دارد که خودِ HTTPS را مدیریت می‌کند و درخواست را با HTTP ساده به
        | داخل کانتینر می‌فرستد. بدون این تنظیم، لاراول به‌اشتباه فکر
        | می‌کند درخواست http بوده (نه https)، و همین باعث می‌شود URLهای
        | امضاشده‌ی موقت (مثل آدرس آپلود فایل Livewire) نامعتبر تشخیص داده
        | شوند — چون امضا برای https ساخته شده ولی اعتبارسنجی روی http
        | انجام می‌شود. با اعتماد به هدرهای X-Forwarded-* از همه‌ی
        | پراکسی‌ها ('*')، لاراول اسکیم واقعی (https) را درست تشخیص می‌دهد.
        |--------------------------------------------------------------------------
        */

        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB
        );

        /*
        |--------------------------------------------------------------------------
        | Web Middleware
        |--------------------------------------------------------------------------
        */

        $middleware->web(append: [
            EnsureAgreementAccepted::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        /*
        |--------------------------------------------------------------------------
        | Validation Exception
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (ValidationException $e,$request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }
            return ApiResponse::validation($e->errors());
        });

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (AuthenticationException $e,$request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }
            return ApiResponse::unauthorized();
        });

        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (AccessDeniedHttpException $e,$request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }
            return ApiResponse::forbidden();
        });

        /*
        |--------------------------------------------------------------------------
        | Model Not Found
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (ModelNotFoundException $e,$request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }
            return ApiResponse::notFound('اطلاعات درخواستی پیدا نشد.');
        });

        /*
        |--------------------------------------------------------------------------
        | OTP Exceptions
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (InvalidLoginTokenException $e,$request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }
            return ApiResponse::error($e->getMessage(),null,404);
        });

        $exceptions->render(function (InvalidOtpException $e,$request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }
            return ApiResponse::error($e->getMessage(),null,422);
        });

        $exceptions->render(function (ExpiredOtpException $e,$request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }
            return ApiResponse::error($e->getMessage(),null,410);
        });

        $exceptions->render(function (OtpAlreadyUsedException $e,$request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }
            return ApiResponse::error($e->getMessage(),null,409);
        });

        $exceptions->render(function (OtpAttemptsExceededException $e,$request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }
            return ApiResponse::error($e->getMessage(),null,429);
        });

        /*
        |--------------------------------------------------------------------------
        | Internal Server Error
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (\Throwable $e,$request) {
            \Illuminate\Support\Facades\Log::channel('single')->error(
                '[DIAGNOSTIC] Unhandled exception: ' . get_class($e) . ' — ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()
            );
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }
            $status = ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface)
                ? $e->getStatusCode()
                : 500;
            return ApiResponse::error(
                app()->hasDebugModeEnabled()
                    ? ($e->getMessage() ?: get_class($e) . ' (بدون پیام)')
                    : 'مشکلی در سرور رخ داد. لطفاً کمی بعد دوباره تلاش کنید.',
                null,
                $status
            );
        });

    })
    ->create();
