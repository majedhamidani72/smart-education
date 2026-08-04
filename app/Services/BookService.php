<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;
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
    public function __construct(BookRepositoryInterface $bookRepository)
    {
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
     * دریافت یک کتاب
     */
    public function findById(int $id): ?Book
    {
        return $this->bookRepository->findById($id);
    }

    /**
     * ایجاد کتاب جدید
     */
    public function create(array $data): Book
    {
        return $this->bookRepository->create($data);
    }

    /**
     * بروزرسانی کتاب
     */
    public function update(Book $book, array $data): Book
    {
        return $this->bookRepository->update($book, $data);
    }

    /**
     * حذف نرم کتاب
     */
    public function delete(Book $book): bool
    {
        return $this->bookRepository->delete($book);
    }
}
