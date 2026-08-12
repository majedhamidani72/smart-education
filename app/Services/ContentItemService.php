<?php

namespace App\Services;

use Throwable;
use App\Models\ContentItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\ContentItemRepositoryInterface;

class ContentItemService
{
    /**
     * Repository مربوط به محتوا
     */
    protected ContentItemRepositoryInterface $contentItemRepository;

    public function __construct(
        ContentItemRepositoryInterface $contentItemRepository
    ) {
        $this->contentItemRepository = $contentItemRepository;
    }

    /**
     * دریافت همه محتواها
     */
    public function getAll(): Collection
    {
        return $this->contentItemRepository->getAll();
    }

    /**
     * صفحه‌بندی محتواها
     */
    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->contentItemRepository->paginate(
            $perPage
        );
    }

    /**
     * دریافت یک محتوا
     */
    public function findById(
        int $id
    ): ?ContentItem
    {
        return $this->contentItemRepository->findById(
            $id
        );
    }

    /**
     * ایجاد محتوا
     */
    public function create(
        array $data
    ): ContentItem
    {
        try {

            $data['status'] = 'draft';

            return $this->contentItemRepository->create(
                $data
            );

        } catch (Throwable $e) {

            Log::error('Content item creation failed.', [

                'data' => $data,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * بروزرسانی محتوا
     */
    public function update(
        ContentItem $contentItem,
        array $data
    ): ContentItem
    {
        try {

            return $this->contentItemRepository->update(

                $contentItem,

                $data

            );

        } catch (Throwable $e) {

            Log::error('Content item update failed.', [

                'content_item_id' => $contentItem->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * حذف محتوا
     */
    public function delete(
        ContentItem $contentItem
    ): bool
    {
        try {

            return $this->contentItemRepository->delete(
                $contentItem
            );

        } catch (Throwable $e) {

            Log::error('Content item delete failed.', [

                'content_item_id' => $contentItem->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * ارسال برای بررسی
     */
    public function submitForReview(
        ContentItem $contentItem
    ): ContentItem
    {
        try {

            return $this->contentItemRepository->update(

                $contentItem,

                [

                    'status' => 'pending',

                ]

            );

        } catch (Throwable $e) {

            Log::error('Submit content for review failed.', [

                'content_item_id' => $contentItem->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * تایید محتوا
     */
    public function approve(
        ContentItem $contentItem
    ): ContentItem
    {
        try {

            return $this->contentItemRepository->update(

                $contentItem,

                [

                    'status' => 'approved',

                    'reviewed_by' => Auth::id(),

                ]

            );

        } catch (Throwable $e) {

            Log::error('Content approval failed.', [

                'content_item_id' => $contentItem->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * رد محتوا
     */
    public function reject(
        ContentItem $contentItem,
        string $reason
    ): ContentItem
    {
        try {

            return $this->contentItemRepository->update(

                $contentItem,

                [

                    'status' => 'rejected',

                    'reviewed_by' => Auth::id(),

                    'rejection_reason' => $reason,

                ]

            );

        } catch (Throwable $e) {

            Log::error('Content rejection failed.', [

                'content_item_id' => $contentItem->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }

    /**
     * انتشار محتوا
     */
    public function publish(
        ContentItem $contentItem
    ): ContentItem
    {
        try {

            return $this->contentItemRepository->update(

                $contentItem,

                [

                    'status' => 'published',

                    'published_at' => now(),

                ]

            );

        } catch (Throwable $e) {

            Log::error('Content publish failed.', [

                'content_item_id' => $contentItem->id,

                'error' => $e->getMessage(),

            ]);

            throw $e;
        }
    }
}
