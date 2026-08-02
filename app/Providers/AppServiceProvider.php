<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Eloquent\GradeRepository;
use App\Repositories\Interfaces\GradeRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * ثبت وابستگی‌های پروژه در Service Container
     */
    public function register(): void
    {
        // اتصال Interface به Repository
        $this->app->bind(
            GradeRepositoryInterface::class,
            GradeRepository::class
        );
    }

    /**
     * تنظیمات اولیه برنامه
     */
    public function boot(): void
    {
        //
    }
}
