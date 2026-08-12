<?php

namespace App\Services;

use Throwable;
use App\Models\Book;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\BookRepositoryInterface;

class BookService
{
    /**
     * Repository مربوط به کتاب‌ها
     */
    protected BookRepositoryInterface $bookRepository;

    /**
     * تزریق Repository
     */
    public function __construct(
        BookRepositoryInterface $bookRepository
    ) {
        $this->bookRepository = $bookRepository;
    }

    /**
     * دریافت تمام کتاب‌ها
     */
    public function getAll(): Collection
    {
        return $this->bookRepository->getAll();
    }

    /**
     * صفحه‌بندی کتاب‌ها
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->bookRepository->paginate(
            $perPage
        );
    }

    /**
     * دریافت یک کتاب
     */
    public function findById(
        int $id
    ): ?Book
    {
        return $this->bookRepository->findById(
            $id
        );
    }

    /**
     * ایجاد کتاب جدید
     */
    public function create(
        array $data
    ): Book
    {
        try {

            return $this->bookRepository->create(
                $data
            );

        } catch (Throwable $e) {

            Log::error('Book creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * بروزرسانی کتاب
     */
    public function update(
        Book $book,
        array $data
    ): Book
    {
        try {

            return $this->bookRepository->update(

                $book,

                $data

            );

        } catch (Throwable $e) {

            Log::error('Book update failed.', [

                'book_id' => $book->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * حذف کتاب
     */
    public function delete(
        Book $book
    ): bool
    {
        try {

            return $this->bookRepository->delete(
                $book
            );

        } catch (Throwable $e) {

            Log::error('Book delete failed.', [

                'book_id' => $book->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }
}
