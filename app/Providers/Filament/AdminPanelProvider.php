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
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Blade;
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

            ->brandName('پنل مدیریت درسکا')

            ->login(\App\Filament\Auth\Login::class)

            // این فقط باعث می‌شود لینک «فراموشی رمز عبور» روی
            // صفحه‌ی ورود نمایش داده شود؛ خودِ آدرسی که این لینک
            // به آن می‌رود، توسط Login::getPasswordResetUrl()
            // بازنویسی شده و به فرم پیامکی سفارشی ما می‌رود، نه
            // به صفحات پیش‌فرض ایمیل‌محور خودِ Filament.
            ->passwordReset()

            ->colors([
                'primary' => Color::Blue,
            ])

            ->collapsibleNavigationGroups(true)

            // رنگ ملایمِ فقط روی آیکون‌های هر گروه منو (نه پس‌زمینه
            // یا متن) — تا با رنگ‌های وضعیت جداول (موفق/ناموفق و
            // مشابه) قاطی نشود. چون Filament به‌صورت رسمی رنگ جدا
            // برای هر NavigationGroup نمی‌دهد، این با تزریق مستقیم
            // CSS (بدون نیاز به بیلد جداگانه‌ی npm) انجام شده. اگر
            // بعداً ترتیب گروه‌ها عوض شود، این رنگ‌ها هم باید
            // به‌روزرسانی شوند (چون بر اساس ترتیب، nth-of-type،
            // تشخیص داده می‌شوند).
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn() => Blade::render(<<<'HTML'
                    <style>
                        .fi-sidebar-group:nth-of-type(1) .fi-sidebar-item-icon {
                            color: rgb(99 102 241); /* مدیریت آموزش — بنفش */
                        }
                        .fi-sidebar-group:nth-of-type(2) .fi-sidebar-item-icon {
                            color: rgb(217 70 239); /* آزمون آنلاین — صورتی */
                        }
                        .fi-sidebar-group:nth-of-type(3) .fi-sidebar-item-icon {
                            color: rgb(20 184 166); /* مدیریت کاربران — فیروزه‌ای */
                        }
                        .fi-sidebar-group:nth-of-type(4) .fi-sidebar-item-icon {
                            color: rgb(234 179 8); /* مدیریت مالی — کهربایی */
                        }
                        .fi-sidebar-group:nth-of-type(5) .fi-sidebar-item-icon {
                            color: rgb(100 116 139); /* مدیریت سیستم — خاکستری */
                        }
                    </style>
                HTML)
            )

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

                NavigationGroup::make('مدیریت مالی')
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
