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
    protected BookService $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    /**
     * لیست کتاب‌ها
     */
    public function index()
    {
        $books = $this->bookService->getAll();

        return ApiResponse::success(
            BookResource::collection($books),
            'Books retrieved successfully.'
        );
    }

    /**
     * نمایش یک کتاب
     */
    public function show(Book $book)
    {
        return ApiResponse::success(
            new BookResource($book),
            'Book retrieved successfully.'
        );
    }

    /**
     * ایجاد کتاب
     */
    public function store(StoreBookRequest $request)
    {
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
    public function destroy(Book $book)
    {
        $this->bookService->delete($book);

        return ApiResponse::success(
            null,
            'Book deleted successfully.'
        );
    }
}
