<?php

namespace Database\Seeders;

use App\Models\App;
use App\Models\Book;
use App\Models\ContentItem;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * پاک‌کردن کامل داده‌ی نمایشی — هرچه توسط DemoContentSeeder ساخته
 * شده، از طریق همین دو معلمِ مشخص (شناسایی با شماره‌موبایل) پیدا
 * و حذف می‌شود؛ داده‌های واقعی دیگر پروژه دست‌نخورده می‌مانند.
 *
 * اجرا:
 *   php artisan db:seed --class="Database\\Seeders\\DemoContentCleanerSeeder"
 */
class DemoContentCleanerSeeder extends Seeder
{
    public function run(): void
    {
        // withTrashed چون معلم‌های آزمایشی ممکن است در تلاش‌های
        // ناقص قبلی حذف‌نرم شده باشند — بدون این، پیداشان نمی‌کرد.
        $teacherIds = User::withTrashed()
            ->whereIn('mobile', ['09110000000', '09120000000'])
            ->pluck('id');

        if ($teacherIds->isEmpty()) {
            $this->command->info('داده‌ی نمایشی‌ای پیدا نشد — چیزی برای پاک‌کردن نیست.');
            return;
        }

        $this->command->info('در حال حذف سوالات آزمون‌ها...');

        Question::whereNull('content_item_id')
            ->whereIn('created_by', $teacherIds)
            ->each(function (Question $question) {
                $question->options()->delete();
                $question->quizzes()->detach();
                $question->delete();
            });

        $this->command->info('در حال حذف آزمون‌ها...');

        Quiz::whereIn('created_by', $teacherIds)->delete();

        $this->command->info('در حال حذف نمونه‌سوالات محتوا...');

        Question::whereNotNull('content_item_id')
            ->whereIn('created_by', $teacherIds)
            ->each(function (Question $question) {
                $question->options()->delete();
                $question->delete();
            });

        $this->command->info('در حال حذف محتوای آموزشی (تدریس/نمونه‌سوال)...');

        ContentItem::whereIn('created_by', $teacherIds)->each(function (ContentItem $item) {
            $item->video()?->delete();
            $item->pdfFile()?->delete();
            $item->stepByStep()?->delete();
            $item->delete();
        });

        $this->command->info('در حال حذف اختصاص معلم‌ها...');

        TeacherAssignment::whereIn('teacher_id', $teacherIds)->delete();

        $this->command->info('در حال حذف اپلیکیشن نمایشی (و به‌تبع آن کتاب/فصل/بخش‌هایش)...');

        $app = App::where('slug', 'demo-full')->first();

        if ($app) {

            foreach ($app->appGradeSubjects as $ags) {

                foreach ($ags->books as $book) {

                    foreach ($book->chapters as $chapter) {

                        $chapter->sections()->delete();
                    }

                    $book->chapters()->delete();

                    $book->delete();
                }

                $ags->delete();
            }

            $app->delete();
        }

        $this->command->info('در حال حذف دو معلم نمایشی...');

        // forceDelete تا واقعاً کامل پاک شود، نه فقط حذف نرم —
        // وگرنه دفعه‌ی بعد دوباره همین مشکل «شماره تکراری» پیش می‌آید.
        User::withTrashed()
            ->whereIn('id', $teacherIds)
            ->forceDelete();

        $this->command->info('تمام داده‌ی نمایشی پاک شد ✅');
    }
}
