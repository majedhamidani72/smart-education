<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Book;
use App\Helpers\ApiResponse;
use App\Services\BookService;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;

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
    public function index()
    {
        $this->authorize(
            'viewAny',
            Book::class
        );

        $books = $this->bookService->paginate();

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
    )
    {
        $this->authorize(
            'view',
            $book
        );

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
    )
    {
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
    )
    {
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
    )
    {
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
    )
    {
        // برخلاف متدهای مدیریتی (index/show/store/...)، این مسیر
        // مخصوص خودِ دانش‌آموز است — او باید بتواند قبل از هرگونه
        // خرید، آزادانه معلم‌های یک کتاب را مرور کند. اینجا از
        // مجوز مدیریتی books.view استفاده نمی‌شود (که مخصوص پنل
        // ادمین/معلم است، نه کاربر عادی برنامه).
        $teacherIds = \App\Models\TeacherAssignment::query()
            ->where('book_id', $book->id)
            ->where('is_active', true)
            ->pluck('teacher_id');

        $teachers = \App\Models\User::query()
            ->whereIn('id', $teacherIds)
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            \App\Http\Resources\TeacherResource::collection($teachers),
            'Teachers retrieved successfully.'
        );
    }
}
