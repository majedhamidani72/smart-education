<?php

namespace Database\Seeders;

use App\Models\App;
use App\Models\AppGradeSubject;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\ContentItem;
use App\Models\ContentType;
use App\Models\Grade;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * سیدر داده‌ی نمایشی/تست — همه‌ی پایه‌ها (۱ تا ۱۲)
 * --------------------------------------------------------------------
 * هدف: پر کردن پروژه با داده‌ی واقع‌نما (نام کتاب‌های واقعی درسی
 * ایران) تا کل وب‌سایت — از پایه اول تا دوازدهم، با تدریس، نمونه
 * سوال، و آزمون آنلاین در هر سطح (بخش/فصل/کل کتاب) — قابل مشاهده
 * و تست باشد. این داده صرفاً برای نمایش شکل سایت است، نه محتوای
 * واقعی نهایی.
 *
 * حذف کامل بعداً: چون همه‌چیز از طریق دو معلمِ مشخص (با شماره
 * موبایل قابل‌شناسایی) ساخته می‌شود، برای پاک‌کردنِ همه‌ی این
 * داده‌ها کافی است این دستور را بزنید:
 *
 *   php artisan db:seed --class=Database\\Seeders\\DemoContentCleanerSeeder
 *
 * (فایل آن کنار همین سیدر ساخته شده است.)
 */
class DemoContentSeeder extends Seeder
{
    protected User $teacherElementary;

    protected User $teacherSecondary;

    protected App $app;

    protected array $contentTypeIds = [];

    /*
    |--------------------------------------------------------------------------
    | ساختار کتاب‌های درسی واقعی ایران — به تفکیک هر پایه
    |--------------------------------------------------------------------------
    */
    protected array $curriculum = [

        1 => ['title' => 'اول', 'books' => ['فارسی', 'ریاضی', 'علوم تجربی', 'هدیه‌های آسمان', 'نگارش']],

        2 => ['title' => 'دوم', 'books' => ['فارسی', 'ریاضی', 'علوم تجربی', 'هدیه‌های آسمان', 'نگارش']],

        3 => ['title' => 'سوم', 'books' => ['فارسی', 'ریاضی', 'علوم تجربی', 'هدیه‌های آسمان', 'مطالعات اجتماعی', 'نگارش']],

        4 => ['title' => 'چهارم', 'books' => ['فارسی', 'ریاضی', 'علوم تجربی', 'هدیه‌های آسمان', 'مطالعات اجتماعی', 'نگارش']],

        5 => ['title' => 'پنجم', 'books' => ['فارسی', 'ریاضی', 'علوم تجربی', 'هدیه‌های آسمان', 'مطالعات اجتماعی', 'نگارش', 'قرآن']],

        6 => ['title' => 'ششم', 'books' => ['فارسی', 'ریاضی', 'علوم تجربی', 'هدیه‌های آسمان', 'مطالعات اجتماعی', 'نگارش', 'قرآن', 'کار و فناوری']],

        7 => ['title' => 'هفتم', 'books' => ['فارسی', 'عربی', 'ریاضی', 'علوم تجربی', 'مطالعات اجتماعی', 'پیام‌های آسمان', 'زبان انگلیسی', 'کار و فناوری']],

        8 => ['title' => 'هشتم', 'books' => ['فارسی', 'عربی', 'ریاضی', 'علوم تجربی', 'مطالعات اجتماعی', 'پیام‌های آسمان', 'زبان انگلیسی', 'کار و فناوری']],

        9 => ['title' => 'نهم', 'books' => ['فارسی', 'عربی', 'ریاضی', 'علوم تجربی', 'مطالعات اجتماعی', 'پیام‌های آسمان', 'زبان انگلیسی', 'آمادگی دفاعی']],

        10 => ['title' => 'دهم', 'books' => ['ادبیات فارسی', 'عربی', 'دین و زندگی', 'زبان انگلیسی', 'ریاضی', 'فیزیک', 'شیمی', 'هندسه']],

        11 => ['title' => 'یازدهم', 'books' => ['ادبیات فارسی', 'عربی', 'دین و زندگی', 'زبان انگلیسی', 'حسابان', 'فیزیک', 'شیمی', 'هندسه']],

        12 => ['title' => 'دوازدهم', 'books' => ['ادبیات فارسی', 'عربی', 'دین و زندگی', 'زبان انگلیسی', 'حسابان', 'فیزیک', 'شیمی', 'ریاضی و آمار']],

    ];

    public function run(): void
    {
        $this->command->info('شروع ساخت داده‌ی نمایشی برای همه‌ی پایه‌ها...');

        $this->setupTeachers();

        $this->setupApp();

        $this->setupContentTypes();

        foreach ($this->curriculum as $gradeNumber => $data) {

            $this->command->info("پایه {$data['title']}...");

            $grade = Grade::firstOrCreate(
                ['grade_number' => $gradeNumber],
                ['title' => $data['title']]
            );

            $teacher = $gradeNumber <= 6 ? $this->teacherElementary : $this->teacherSecondary;

            foreach ($data['books'] as $index => $bookTitle) {

                $this->buildBook($grade, $bookTitle, $teacher, $index);
            }
        }

        $this->command->info('تمام شد ✅ — برای پاک‌کردن همه‌ی این داده‌ها بعداً:');
        $this->command->info('php artisan db:seed --class="Database\\Seeders\\DemoContentCleanerSeeder"');
    }

    protected function setupTeachers(): void
    {
        $this->teacherElementary = $this->findOrCreateTeacher(
            '09110000000',
            'خالد قاضی'
        );

        $this->teacherSecondary = $this->findOrCreateTeacher(
            '09120000000',
            'محمد محمدی'
        );
    }

    /**
     * جست‌وجوی کاربر حتی اگر قبلاً حذف نرم شده باشد (withTrashed) —
     * چون firstOrCreate عادی رکوردهای حذف‌شده را نمی‌بیند و باعث
     * خطای «شماره تکراری» می‌شود. اگر پیدا شد ولی حذف‌شده بود،
     * بازیابی می‌شود؛ اگر اصلاً پیدا نشد، تازه ساخته می‌شود.
     */
    protected function findOrCreateTeacher(string $mobile, string $name): User
    {
        $user = User::withTrashed()->where('mobile', $mobile)->first();

        if ($user) {

            if ($user->trashed()) {
                $user->restore();
            }

        } else {

            $user = User::create([
                'mobile' => $mobile,
                'name' => $name,
                'password' => bcrypt(Str::random(16)),
                'is_active' => true,
            ]);
        }

        if (! $user->hasRole('Teacher')) {
            $user->assignRole('Teacher');
        }

        return $user;
    }

    protected function setupApp(): void
    {
        $this->app = App::firstOrCreate(
            ['slug' => 'demo-full'],
            [
                'title' => 'اسمارت اجوکیشن — نسخه‌ی نمایشی',
                'is_active' => true,
            ]
        );
    }

    protected function setupContentTypes(): void
    {
        $this->contentTypeIds = ContentType::pluck('id', 'slug')->toArray();
    }

    protected function buildBook(Grade $grade, string $bookTitle, User $teacher, int $sortOrder): void
    {
        $subject = Subject::firstOrCreate(
            ['slug' => Str::slug($bookTitle.'-'.$grade->grade_number, '-')],
            ['title' => $bookTitle]
        );

        $appGradeSubject = AppGradeSubject::firstOrCreate([
            'app_id' => $this->app->id,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
        ], [
            'is_active' => true,
            'sort_order' => $sortOrder,
        ]);

        $book = Book::firstOrCreate([
            'app_grade_subject_id' => $appGradeSubject->id,
        ], [
            'title' => $bookTitle,
            'slug' => Str::slug($bookTitle.'-'.$grade->grade_number.'-'.Str::random(4), '-'),
            'is_active' => true,
            'sort_order' => $sortOrder,
        ]);

        // اختصاص معلم — برای دو کتاب اول هر پایه‌ی متوسطه، هر دو
        // معلم را اختصاص می‌دهیم تا سناریوی «چند معلم برای یک
        // کتاب» هم روی سایت قابل تست باشد.
        TeacherAssignment::firstOrCreate([
            'teacher_id' => $teacher->id,
            'book_id' => $book->id,
        ], [
            'commission_percentage' => 60,
            'is_active' => true,
        ]);

        if ($grade->grade_number > 6 && $sortOrder < 2) {

            TeacherAssignment::firstOrCreate([
                'teacher_id' => $this->teacherElementary->id,
                'book_id' => $book->id,
            ], [
                'commission_percentage' => 60,
                'is_active' => true,
            ]);
        }

        // ۳ فصل، هرکدام با ۲ بخش — کافی برای نمایش کامل سلسله‌مراتب
        // بدون این‌که داده بیش‌ازحد حجیم شود.
        for ($chapterNum = 1; $chapterNum <= 3; $chapterNum++) {

            $chapter = Chapter::firstOrCreate([
                'book_id' => $book->id,
                'sort_order' => $chapterNum,
            ], [
                'title' => 'فصل '.$chapterNum,
                'slug' => Str::slug($book->slug.'-chapter-'.$chapterNum, '-'),
                'is_active' => true,
            ]);

            $sectionIds = [];

            for ($sectionNum = 1; $sectionNum <= 2; $sectionNum++) {

                $section = Section::firstOrCreate([
                    'chapter_id' => $chapter->id,
                    'sort_order' => $sectionNum,
                ], [
                    'title' => 'بخش '.$sectionNum,
                    'slug' => Str::slug($chapter->slug.'-section-'.$sectionNum, '-'),
                    'is_active' => true,
                ]);

                $sectionIds[] = $section->id;

                $this->buildContentForSection($section, $chapter, $book, $teacher);

                $this->buildQuiz($section, 'section', $teacher, $bookTitle.' — '.$chapter->title.' — '.$section->title);
            }

            $this->buildQuiz($chapter, 'chapter', $teacher, $bookTitle.' — آزمون '.$chapter->title);
        }

        $this->buildQuiz($book, 'book', $teacher, 'آزمون جامع '.$bookTitle);
    }

    protected function buildContentForSection(Section $section, Chapter $chapter, Book $book, User $teacher): void
    {
        // تدریس (ویدئو)
        if (isset($this->contentTypeIds['teaching'])) {

            $item = ContentItem::create([
                'section_id' => $section->id,
                'chapter_id' => $chapter->id,
                'content_type_id' => $this->contentTypeIds['teaching'],
                'created_by' => $teacher->id,
                'reviewed_by' => $teacher->id,
                'title' => 'تدریس '.$section->title.' — '.$chapter->title,
                'slug' => Str::slug($section->slug.'-teaching-'.Str::random(4), '-'),
                'is_free' => $section->sort_order === 1 && $chapter->sort_order === 1,
                'status' => 'approved',
                'sort_order' => 1,
                'published_at' => now(),
            ]);

            Video::create([
                'content_item_id' => $item->id,
                'uploaded_by' => $teacher->id,
                'directory' => 'demo',
                'filename' => 'demo-placeholder.mp4',
                'original_name' => 'demo-placeholder.mp4',
                'extension' => 'mp4',
                'mime_type' => 'video/mp4',
                'file_size' => 0,
                'duration' => 600,
                'quality' => '720p',
            ]);
        }

        // نمونه سوال (این یعنی خودِ سوالات بانک سوالات، وصل به
        // همین content_item — نه فایل PDF جدا)
        if (isset($this->contentTypeIds['sample_questions'])) {

            $item = ContentItem::create([
                'section_id' => $section->id,
                'chapter_id' => $chapter->id,
                'content_type_id' => $this->contentTypeIds['sample_questions'],
                'created_by' => $teacher->id,
                'reviewed_by' => $teacher->id,
                'title' => 'نمونه سوال '.$section->title,
                'slug' => Str::slug($section->slug.'-sample-'.Str::random(4), '-'),
                'is_free' => false,
                'status' => 'approved',
                'sort_order' => 2,
                'published_at' => now(),
            ]);

            $this->buildSampleQuestions($item, $book, $chapter, $section, $teacher);
        }
    }

    protected function buildSampleQuestions(ContentItem $item, Book $book, Chapter $chapter, Section $section, User $teacher): void
    {
        for ($i = 1; $i <= 3; $i++) {

            $question = Question::create([
                'content_item_id' => $item->id,
                'book_id' => $book->id,
                'chapter_id' => $chapter->id,
                'section_id' => $section->id,
                'created_by' => $teacher->id,
                'reviewed_by' => $teacher->id,
                'question_text' => 'سوال نمونه‌ی '.$i.' — '.$section->title,
                'explanation' => 'توضیح پاسخ سوال '.$i,
                'recommendation_text' => 'برای مرور بیشتر، تدریس همین بخش را دوباره ببین.',
                'difficulty' => ['easy', 'medium', 'hard'][($i - 1) % 3],
                'default_score' => 1,
                'status' => 'approved',
                'is_active' => true,
            ]);

            foreach (['گزینه یک', 'گزینه دو', 'گزینه سه', 'گزینه چهار'] as $optIndex => $optionText) {

                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => $optIndex === 0,
                ]);
            }
        }
    }

    protected function buildQuiz(mixed $quizable, string $level, User $teacher, string $title): void
    {
        $quiz = Quiz::create([
            'quizable_type' => get_class($quizable),
            'quizable_id' => $quizable->id,
            'created_by' => $teacher->id,
            'reviewed_by' => $teacher->id,
            'title' => $title,
            'questions_count' => 5,
            'passing_percentage' => 50,
            'randomize_questions' => false,
            'randomize_options' => false,
            'show_result' => true,
            'show_correct_answers' => true,
            // فقط اولین آزمون هر سطح رایگان است — بقیه پولی، تا هم
            // حالت رایگان هم حالت «نیاز به خرید» روی سایت قابل تست
            // باشند.
            'is_free' => $quizable->id % 3 === 0,
            'status' => 'active',
            'published_at' => now(),
        ]);

        for ($i = 1; $i <= 5; $i++) {

            $question = Question::create([
                'created_by' => $teacher->id,
                'reviewed_by' => $teacher->id,
                'question_text' => $title.' — سوال '.$i,
                'explanation' => 'توضیح پاسخ سوال '.$i,
                'recommendation_text' => 'این بخش را دوباره مرور کن.',
                'difficulty' => 'medium',
                'default_score' => 1,
                'status' => 'approved',
                'is_active' => true,
            ]);

            $question->quizzes()->attach($quiz->id);

            foreach (['گزینه یک', 'گزینه دو', 'گزینه سه', 'گزینه چهار'] as $optIndex => $optionText) {

                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => $optIndex === 0,
                ]);
            }
        }
    }
}
