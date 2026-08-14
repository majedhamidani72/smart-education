<?php

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

        $user = auth()->user();


        if (
            $user
            &&
            $user->must_change_password
            &&
            ! $user->hasRole('SuperAdmin')
            &&
            ! $request->is('admin/change-password')
            &&
            ! $request->is('admin/logout')
        ) {

            return redirect(
                '/admin/change-password'
            );

        }


        return $next($request);
    }
}
