<?php

namespace App\Providers\Filament;

use App\Http\Middleware\CheckPasswordChange;
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

                // ترتیب و آیکون گروه‌های اصلی منو
                // این آرایه فقط ترتیب نمایش را کنترل می‌کند؛
                // هر Resource با ملکیت navigationGroup خودش
                // به یکی از این گروه‌ها متصل می‌شود.

                NavigationGroup::make('مدیریت آموزش')
                    ->icon('heroicon-o-academic-cap')
                    ->collapsed(false),

                NavigationGroup::make('آزمون آنلاین')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->collapsed(false),

                NavigationGroup::make('مدیریت کاربران')
                    ->icon('heroicon-o-users')
                    ->collapsed(false),

                NavigationGroup::make('مدیریت سیستم')
                    ->icon('heroicon-o-cog-6-tooth')
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

            ]);
    }
}
