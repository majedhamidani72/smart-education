<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Eloquent\GradeRepository;
use App\Repositories\Interfaces\GradeRepositoryInterface;
use App\Repositories\Eloquent\SubjectRepository;
use App\Repositories\Interfaces\SubjectRepositoryInterface;
use App\Repositories\Eloquent\BookRepository;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Eloquent\ChapterRepository;
use App\Repositories\Interfaces\ChapterRepositoryInterface;
use App\Repositories\Eloquent\SectionRepository;
use App\Repositories\Interfaces\SectionRepositoryInterface;
use App\Repositories\Eloquent\ContentItemRepository;
use App\Repositories\Interfaces\ContentItemRepositoryInterface;
use App\Repositories\Eloquent\VideoRepository;
use App\Repositories\Interfaces\VideoRepositoryInterface;
use App\Repositories\Eloquent\PdfFileRepository;
use App\Repositories\Interfaces\PdfFileRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * ثبت وابستگی‌های پروژه در Service Container
     */
    public function register(): void
    {
        // اتصال Interface به Repository
        $this->app->bind(GradeRepositoryInterface::class,GradeRepository::class);


        // اتصال Interface به Repository
        $this->app->bind(SubjectRepositoryInterface::class,SubjectRepository::class);


        $this->app->bind(BookRepositoryInterface::class, BookRepository::class);

        $this->app->bind(ChapterRepositoryInterface::class, ChapterRepository::class);

        $this->app->bind(SectionRepositoryInterface::class, SectionRepository::class);

        $this->app->bind(ContentItemRepositoryInterface::class, ContentItemRepository::class);

        $this->app->bind(VideoRepositoryInterface::class,VideoRepository::class);

        $this->app->bind(PdfFileRepositoryInterface::class,PdfFileRepository::class);


    }


    //تنظیمات اولیه برنامه
    public function boot(): void
    {
        //
    }
}
