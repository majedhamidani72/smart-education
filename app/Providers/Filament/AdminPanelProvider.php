<?php

namespace App\Providers\Filament;

use App\Http\Middleware\CheckPasswordChange;
use App\Http\Middleware\EnsureAgreementAccepted;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(
        Panel $panel
    ): Panel {

        return $panel

            ->default()

            ->id('admin')

            ->path('admin')

            ->login(\App\Filament\Auth\Login::class)

            ->colors([
                'primary' => Color::Blue,
            ])

            ->navigationGroups([

                // ترتیب گروه‌های اصلی منو
                // این آرایه فقط ترتیب نمایش را کنترل می‌کند؛
                // هر Resource با ملکیت navigationGroup خودش
                // به یکی از این گروه‌ها متصل می‌شود.
                //
                // نکته مهم: در Filament، یا گروه می‌تواند آیکون
                // داشته باشد یا آیتم‌های داخل آن (Resourceها) —
                // نه هر دو با هم. چون هر Resource همین حالا
                // navigationIcon مخصوص به خودش را دارد،
                // اینجا از icon() روی گروه استفاده نمی‌کنیم.

                NavigationGroup::make('مدیریت آموزش')
                    ->collapsed(false),

                NavigationGroup::make('آزمون آنلاین')
                    ->collapsed(false),

                NavigationGroup::make('مدیریت کاربران')
                    ->collapsed(false),

                NavigationGroup::make('مدیریت سیستم')
                    ->collapsed(false),

            ])

            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )

            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )

            ->pages([
                Pages\Dashboard::class,
            ])

            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )

            ->widgets([
                Widgets\AccountWidget::class,
            ])

            ->middleware([

                EncryptCookies::class,

                AddQueuedCookiesToResponse::class,

                StartSession::class,

                AuthenticateSession::class,

                ShareErrorsFromSession::class,

                VerifyCsrfToken::class,

                SubstituteBindings::class,

                DisableBladeIconComponents::class,

                DispatchServingFilamentEvent::class,

            ])

            ->authMiddleware([

                Authenticate::class,

                CheckPasswordChange::class,

                // این پنل از گروه میدل‌ور استاندارد "web" استفاده
                // نمی‌کند (بالاتر، لیست میدل‌ورها را دستی مشخص
                // کرده‌ایم)، پس هر میدل‌ور سراسری که فقط به گروه
                // "web" اضافه شده باشد (مثل همین مورد در
                // bootstrap/app.php) هرگز روی مسیرهای این پنل
                // اجرا نمی‌شود. برای همین اینجا هم صریحاً اضافه‌اش
                // می‌کنیم. ترتیب مهم است: باید بعد از
                // CheckPasswordChange بیاید تا اول تغییر رمز اجباری
                // انجام شود، بعد قرارداد همکاری بررسی شود.
                EnsureAgreementAccepted::class,

            ]);
    }
}
