<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\TeacherResource;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\Section;
use App\Services\QuizTemplateService;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\BookService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Service
     */
    protected BookService $bookService;

    /**
     * Constructor
     */
    public function __construct(
        BookService $bookService
    ) {
        $this->bookService = $bookService;
    }

    /**
     * لیست کتاب‌ها
     */
    public function index(
        Request $request
    ) {
        // مرور کتاب‌ها آزاد است — نیازی به مجوز مدیریتی نیست.

        // فیلتر اختیاری بر اساس پایه — برای مسیر «پایه → کتاب»ی
        // که در وب‌سایت/اپ برای پایه‌های هفتم تا دوازدهم استفاده
        // می‌شود.
        if ($request->filled('grade_id')) {

            $books = Book::query()
                ->where('is_active', true)
                ->whereHas('appGradeSubject', fn ($q) => $q->where('grade_id', $request->query('grade_id')))
                ->orderBy('sort_order')
                ->paginate();

        } else {

            $books = $this->bookService->paginate();
        }

        return ApiResponse::success(
            BookResource::collection($books),
            'Books retrieved successfully.'
        );
    }

    /**
     * نمایش یک کتاب
     */
    public function show(
        Book $book
    ) {
        // مرور کتاب‌ها آزاد است — نیازی به مجوز مدیریتی نیست.

        return ApiResponse::success(
            new BookResource($book),
            'Book retrieved successfully.'
        );
    }

    /**
     * ایجاد کتاب
     */
    public function store(
        StoreBookRequest $request
    ) {
        $this->authorize(
            'create',
            Book::class
        );

        $book = $this->bookService->create(
            $request->validated()
        );

        return ApiResponse::success(
            new BookResource($book),
            'Book created successfully.',
            201
        );
    }

    /**
     * بروزرسانی کتاب
     */
    public function update(
        UpdateBookRequest $request,
        Book $book
    ) {
        $this->authorize(
            'update',
            $book
        );

        $book = $this->bookService->update(
            $book,
            $request->validated()
        );

        return ApiResponse::success(
            new BookResource($book),
            'Book updated successfully.'
        );
    }

    /**
     * حذف نرم کتاب
     */
    public function destroy(
        Book $book
    ) {
        $this->authorize(
            'delete',
            $book
        );

        $this->bookService->delete(
            $book
        );

        return ApiResponse::success(
            null,
            'Book deleted successfully.'
        );
    }

    /**
     * معلم‌هایی که این کتاب مشخص را تدریس می‌کنند — برای پایه‌های
     * ۷ تا ۱۲: اگر فقط یک معلم برگردد، اپلیکیشن مستقیم وارد
     * محتوای او می‌شود؛ اگر چند معلم بود، دانش‌آموز از این لیست
     * یکی را انتخاب می‌کند.
     */
    public function teachers(
        Book $book
    ) {
        // برخلاف متدهای مدیریتی (index/show/store/...)، این مسیر
        // مخصوص خودِ دانش‌آموز است — او باید بتواند قبل از هرگونه
        // خرید، آزادانه معلم‌های یک کتاب را مرور کند. اینجا از
        // مجوز مدیریتی books.view استفاده نمی‌شود (که مخصوص پنل
        // ادمین/معلم است، نه کاربر عادی برنامه).
        $teacherIds = TeacherAssignment::query()
            ->where('book_id', $book->id)
            ->where('is_active', true)
            ->pluck('teacher_id');

        $teachers = User::query()
            ->whereIn('id', $teacherIds)
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            TeacherResource::collection($teachers),
            'Teachers retrieved successfully.'
        );
    }

    /**
     * سازماندهی آزمون‌های یک کتاب — به تفکیک بخش/فصل/کل کتاب.
     * این دقیقاً همان برگ برنده‌ای است که پروژه نسبت به سایت‌های
     * مشابه دارد: نمایش شفاف اینکه هر آزمون دقیقاً برای کدام سطح
     * (بخش خاص، فصل، یا کل کتاب) طراحی شده — بدون نیاز به ورود،
     * چون فقط سازماندهی/تعداد نشان داده می‌شود، نه خودِ سوالات.
     */
    public function quizSummary(
        Book $book,
        Request $request
    ) {
        // هر بار ساختار کتاب تغییر کند (درس/بخش/فصل جدید)، نمونه‌های
        // اجرایی الگوها بدون دخالت مدیر ساخته و همگام می‌شوند.
        app(QuizTemplateService::class)->syncBook($book);
        // چون این مسیر بدون ورود هم قابل مرور است، اگر کاربر
        // وارد شده باشد (توکن معتبر فرستاده)، اینجا تشخیص داده
        // می‌شود — تا has_access دقیق برای همان کاربر محاسبه شود.
        // چون این مسیر پشت auth:sanctum نیست، $request->user()
        // همیشه خالی است؛ باید مستقیم از گارد sanctum خوانده شود.
        $user = auth('sanctum')->user();

        $hasAccess = fn ($quiz) => $quiz->is_free
            || ($user && $user->hasAccessToQuiz($quiz));

        $chapterIds = $book->chapters()->pluck('id');

        $sectionIds = Section::whereIn('chapter_id', $chapterIds)->pluck('id');

        $baseQuery = fn () => Quiz::query()
            ->where('is_template', false)
            ->where('status', 'active');

        $sectionQuizzes = $baseQuery()
            ->where('quizable_type', Section::class)
            ->whereIn('quizable_id', $sectionIds)
            ->with('quizable.chapter')
            ->get();

        $chapterQuizzes = $baseQuery()
            ->where('quizable_type', Chapter::class)
            ->whereIn('quizable_id', $chapterIds)
            ->with('quizable')
            ->get();

        $bookQuizzes = $baseQuery()
            ->where('quizable_type', Book::class)
            ->where('quizable_id', $book->id)
            ->get();

        return ApiResponse::success([

            'section' => $sectionQuizzes->map(fn ($q) => [
                'id' => $q->id,
                'title' => $q->title,
                'section_id' => $q->quizable_id,
                'chapter_id' => $q->quizable?->chapter_id,
                'section_title' => $q->quizable?->title,
                'chapter_title' => $q->quizable?->chapter?->title,
                'question_count' => $q->questions_count,
                'is_free' => $q->is_free,
                'has_access' => $hasAccess($q),
            ]),

            'chapter' => $chapterQuizzes->map(fn ($q) => [
                'id' => $q->id,
                'title' => $q->title,
                'chapter_id' => $q->quizable_id,
                'chapter_title' => $q->quizable?->title,
                'question_count' => $q->questions_count,
                'is_free' => $q->is_free,
                'has_access' => $hasAccess($q),
            ]),

            'book' => $bookQuizzes->map(fn ($q) => [
                'id' => $q->id,
                'title' => $q->title,
                'question_count' => $q->questions_count,
                'is_free' => $q->is_free,
                'has_access' => $hasAccess($q),
            ]),

        ], 'Quiz summary retrieved successfully.');
    }
}
