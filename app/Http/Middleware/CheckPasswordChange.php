<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChange
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | کاربر وارد نشده است
        |--------------------------------------------------------------------------
        */

        if (! $user) {

            return $next($request);

        }

        /*
        |--------------------------------------------------------------------------
        | سوپر ادمین نیاز به اجبار تغییر رمز ندارد
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole('SuperAdmin')) {

            return $next($request);

        }

        /*
        |--------------------------------------------------------------------------
        | قبلاً رمز را تغییر داده است
        |--------------------------------------------------------------------------
        */

        if (! $user->must_change_password) {

            return $next($request);

        }

        /*
        |--------------------------------------------------------------------------
        | مسیرهای مجاز
        |--------------------------------------------------------------------------
        */

        if (

            $request->routeIs('filament.admin.pages.change-password')

            ||

            $request->routeIs('filament.admin.auth.logout')

        ) {

            return $next($request);

        }

        /*
        |--------------------------------------------------------------------------
        | اجبار تغییر رمز
        |--------------------------------------------------------------------------
        */

        return to_route(
            'filament.admin.pages.change-password'
        );

    }
}
