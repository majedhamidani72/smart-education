<?php

namespace App\Services;

use App\Models\ContentItem;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\ContentItemRepositoryInterface;

class ContentItemService
{
    public function __construct(
        protected ContentItemRepositoryInterface $contentItemRepository
    ) {
    }

    /**
     * دریافت همه محتواها
     */
    public function getAll(): Collection
    {
        return $this->contentItemRepository->getAll();
    }

    /**
     * دریافت یک محتوا
     */
    public function findById(int $id): ?ContentItem
    {
        return $this->contentItemRepository->findById($id);
    }

    /**
     * ایجاد محتوا
     */
    public function create(array $data): ContentItem
    {
        $data['status'] = 'draft';

        return $this->contentItemRepository->create($data);
    }

    /**
     * بروزرسانی محتوا
     */
    public function update(
        ContentItem $contentItem,
        array $data
    ): ContentItem
    {
        return $this->contentItemRepository
            ->update($contentItem, $data);
    }

    /**
     * حذف محتوا
     */
    public function delete(
        ContentItem $contentItem
    ): bool
    {
        return $this->contentItemRepository
            ->delete($contentItem);
    }

    /**
     * ارسال برای بررسی
     */
    public function submitForReview(
        ContentItem $contentItem
    ): ContentItem
    {
        return $this->contentItemRepository->update(
            $contentItem,
            [

                'status' => 'pending',

            ]
        );
    }

    /**
     * تایید محتوا
     */
    public function approve(
        ContentItem $contentItem
    ): ContentItem
    {
        return $this->contentItemRepository->update(
            $contentItem,
            [

                'status' => 'approved',

                'reviewed_by' => auth()->id(),

            ]
        );
    }

    /**
     * رد محتوا
     */
    public function reject(
        ContentItem $contentItem
    ): ContentItem
    {
        return $this->contentItemRepository->update(
            $contentItem,
            [

                'status' => 'rejected',

                'reviewed_by' => auth()->id(),

            ]
        );
    }

    /**
     * انتشار محتوا
     */
    public function publish(
        ContentItem $contentItem
    ): ContentItem
    {
        return $this->contentItemRepository->update(
            $contentItem,
            [

                'status' => 'published',

                'published_at' => now(),

            ]
        );
    }
}
