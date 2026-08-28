<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Grade;
use App\Models\Plan;
use App\Models\Subscription;
use Filament\Widgets\ChartWidget;

/**
 * تعداد دانش‌آموزان با دسترسی فعال، به تفکیک پایه.
 * --------------------------------------------------------------------
 * چون کاربر مستقیماً به یک پایه وصل نیست (بلکه از طریق خرید پلن
 * کتاب یا پلن کل‌پایه)، اینجا بر اساس همان مسیر واقعی دسترسی
 * (User::hasAccessToContentItem) شمارش می‌شود: هر اشتراک فعال،
 * یا مستقیم به یک پایه وصل است، یا از طریق کتاب به پایه‌ی آن
 * کتاب می‌رسد.
 */
class StudentsPerGradeWidget extends ChartWidget
{
    protected static ?string $heading = 'دانش‌آموزان فعال به تفکیک پایه';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['SuperAdmin', 'Admin']) ?? false;
    }


    protected function getData(): array
    {
        $grades = Grade::query()
            ->orderBy('grade_number')
            ->get();

        $activeSubscriptions = Subscription::query()
            ->where('status', 'active')
            ->where('expires_at', '>=', now())
            ->with('plan')
            ->get();

        // شناسه‌ی کتاب → پایه، فقط یک‌بار محاسبه می‌شود تا برای
        // هر اشتراک دوباره کوئری نزنیم.
        $bookGradeMap = Book::query()
            ->with('appGradeSubject')
            ->get()
            ->mapWithKeys(fn(Book $book) => [
                $book->id => $book->appGradeSubject?->grade_id,
            ]);

        $countPerGrade = [];
        $seenPerGrade = [];

        foreach ($activeSubscriptions as $subscription) {

            $plan = $subscription->plan;

            if (! $plan) {
                continue;
            }

            $gradeId = match ($plan->planable_type) {

                Grade::class => $plan->planable_id,

                Book::class => $bookGradeMap[$plan->planable_id] ?? null,

                default => null,
            };

            if (! $gradeId) {
                continue;
            }

            // هر دانش‌آموز در هر پایه فقط یک‌بار شمرده شود، حتی اگر
            // چند اشتراک فعال برای همان پایه داشته باشد.
            $seenPerGrade[$gradeId] ??= [];

            if (in_array($subscription->user_id, $seenPerGrade[$gradeId], true)) {
                continue;
            }

            $seenPerGrade[$gradeId][] = $subscription->user_id;

            $countPerGrade[$gradeId] = ($countPerGrade[$gradeId] ?? 0) + 1;
        }

        return [

            'datasets' => [
                [
                    'label' => 'تعداد دانش‌آموز',
                    'data' => $grades->map(fn(Grade $g) => $countPerGrade[$g->id] ?? 0)->toArray(),
                    'backgroundColor' => '#7c3aed',
                    'borderRadius' => 6,
                ],
            ],

            'labels' => $grades->pluck('title')->toArray(),

        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
