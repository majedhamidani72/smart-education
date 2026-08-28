<?php

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\Section;
use App\Services\QuizTemplateService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $groups = Quiz::query()->where('is_template', false)->get()->groupBy(function (Quiz $quiz) {
            $bookId = match ($quiz->quizable_type) {
                Book::class => $quiz->quizable_id,
                Chapter::class => Chapter::find($quiz->quizable_id)?->book_id,
                Section::class => Section::find($quiz->quizable_id)?->chapter?->book_id,
                default => null,
            };
            return $bookId ? $bookId.'|'.$quiz->quizable_type : null;
        })->forget('');

        foreach ($groups as $key => $quizzes) {
            [$bookId, $scope] = explode('|', $key, 2);
            if (Quiz::query()->where('is_template', true)->where('template_book_id', $bookId)
                ->where('template_scope', $scope)->exists()) {
                continue;
            }

            $source = $quizzes->first();
            $template = $source->replicate();
            $template->forceFill([
                'title' => 'تنظیم مشترک آزمون‌های '.Book::find($bookId)?->title,
                'is_template' => true,
                'template_id' => null,
                'template_book_id' => $bookId,
                'template_scope' => $scope,
                'quizable_type' => Book::class,
                'quizable_id' => $bookId,
            ])->save();

            app(QuizTemplateService::class)->sync($template);
        }
    }

    public function down(): void
    {
        Quiz::query()->whereNotNull('template_id')->update(['template_id' => null]);
        Quiz::query()->where('is_template', true)->delete();
    }
};
