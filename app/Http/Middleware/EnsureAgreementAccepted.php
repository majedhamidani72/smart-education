<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AgreementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgreementAccepted
{
    public function __construct(
        protected AgreementService $agreementService
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole('SuperAdmin')) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | هنوز رمز عبور را تغییر نداده است
        |--------------------------------------------------------------------------
        | اگر کاربر هنوز مجبور به تغییر رمز عبور است، باید اول
        | صفحه‌ی «تغییر رمز» را ببیند، نه قرارداد همکاری را.
        | ترتیب درست: ۱) تغییر رمز  ۲) قرارداد همکاری  ۳) پنل.
        | این چک اجازه می‌دهد CheckPasswordChange (میدل‌ور مخصوص
        | پنل Filament) اول کارش را انجام دهد؛ بعد از تغییر رمز،
        | در درخواست بعدی همین middleware دوباره اجرا می‌شود و
        | این‌بار قرارداد را بررسی می‌کند.
        */

        if ($user->must_change_password) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Free Routes
        |--------------------------------------------------------------------------
        */

        if (
            $request->routeIs('agreement.*')
            || $request->routeIs('logout')
            || $request->routeIs('filament.admin.auth.*')
            || $request->is('livewire/*')
            || $request->is('filament/*')
        ) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Agreement Text Not Exists
        |--------------------------------------------------------------------------
        */

        if (! $this->agreementService->hasAgreementText($user)) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Agreement Not Accepted
        |--------------------------------------------------------------------------
        */

        if (! $this->agreementService->hasAccepted($user)) {
            return redirect()->route('agreement.show');
        }

        return $next($request);
    }
}
