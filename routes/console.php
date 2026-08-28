<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;
use Symfony\Component\Process\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:backup {--keep=14}', function () {
    $directory = storage_path('app/backups');
    File::ensureDirectoryExists($directory);
    $stamp = now()->format('Y-m-d_H-i-s');
    $databaseFile = $directory."/database_{$stamp}.sql";
    $config = config('database.connections.'.config('database.default'));
    $process = new Process([
        'mysqldump', '--host='.$config['host'], '--port='.(string) $config['port'],
        '--user='.$config['username'], '--password='.(string) $config['password'],
        '--single-transaction', '--quick', '--routines', $config['database'],
    ]);
    $process->setTimeout(600);
    $process->run();
    if (! $process->isSuccessful()) {
        $this->error('پشتیبان‌گیری دیتابیس انجام نشد: '.$process->getErrorOutput());
        return 1;
    }
    File::put($databaseFile, $process->getOutput());

    $zipFile = $directory."/files_{$stamp}.zip";
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        $source = storage_path('app/public');
        foreach (File::allFiles($source) as $file) {
            $zip->addFile($file->getPathname(), $file->getRelativePathname());
        }
        $zip->close();
    }

    $keep = max(1, (int) $this->option('keep'));
    collect(File::files($directory))->sortByDesc(fn ($file) => $file->getMTime())
        ->slice($keep * 2)->each(fn ($file) => File::delete($file->getPathname()));
    $this->info('نسخه پشتیبان با موفقیت ساخته شد: '.$stamp);
    return 0;
})->purpose('پشتیبان‌گیری از دیتابیس و فایل‌های عمومی');

Schedule::command('app:backup --keep=14')->dailyAt('02:30')->withoutOverlapping();
