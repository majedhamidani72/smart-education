<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContentItem;
use App\Repositories\Interfaces\ContentItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

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

    /*
    |--------------------------------------------------------------------------
    | Read
    |--------------------------------------------------------------------------
    */

    public function getAll(): Collection
    {
        return $this->contentItemRepository->getAll();
    }

    public function paginate(
        int $perPage = 15
    ): LengthAwarePaginator {

        return $this->contentItemRepository->paginate(
            $perPage
        );
    }

    public function findById(
        int $id
    ): ?ContentItem {

        return $this->contentItemRepository->findById(
            $id
        );
    }

    public function pending(): Collection
    {
        return $this->contentItemRepository

            ->query()

            ->where(
                'status',
                'pending'
            )

            ->get();
    }

    public function approved(): Collection
    {
        return $this->contentItemRepository

            ->query()

            ->where(
                'status',
                'approved'
            )

            ->get();
    }

    public function rejected(): Collection
    {
        return $this->contentItemRepository

            ->query()

            ->where(
                'status',
                'rejected'
            )

            ->get();
    }

    public function published(): Collection
    {
        return $this->contentItemRepository

            ->query()

            ->where(
                'status',
                'published'
            )

            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        array $data
    ): ContentItem {

        try {

            $data['status'] = 'pending';

            return $this->contentItemRepository->create(
                $data
            );
        } catch (Throwable $e) {

            Log::error(

                'Content item creation failed.',

                [

                    'data' => $data,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        ContentItem $contentItem,
        array $data
    ): ContentItem {

        try {

            return $this->contentItemRepository->update(

                $contentItem,

                $data

            );
        } catch (Throwable $e) {

            Log::error(

                'Content item update failed.',

                [

                    'content_item_id' => $contentItem->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        ContentItem $contentItem
    ): bool {

        try {

            return $this->contentItemRepository->delete(
                $contentItem
            );
        } catch (Throwable $e) {

            Log::error(

                'Content item delete failed.',

                [

                    'content_item_id' => $contentItem->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }
        /*
    |--------------------------------------------------------------------------
    | Workflow
    |--------------------------------------------------------------------------
    */

    /**
     * ارسال برای بررسی
     */
    public function submitForReview(
        ContentItem $contentItem
    ): ContentItem {

        try {

            return $this->contentItemRepository->update(

                $contentItem,

                [

                    'status' => 'pending',

                    'reviewed_by' => null,

                    'reviewed_at' => null,

                    'rejection_reason' => null,

                ]

            );
        } catch (Throwable $e) {

            Log::error(

                'Submit content for review failed.',

                [

                    'content_item_id' => $contentItem->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }

    /**
     * تایید محتوا
     */
    public function approve(
        ContentItem $contentItem
    ): ContentItem {

        try {

            return $this->contentItemRepository->update(

                $contentItem,

                [

                    'status' => 'approved',

                    'reviewed_by' => Auth::id(),

                    'reviewed_at' => now(),

                    'rejection_reason' => null,

                ]

            );
        } catch (Throwable $e) {

            Log::error(

                'Content approval failed.',

                [

                    'content_item_id' => $contentItem->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }

    /**
     * رد محتوا
     */
    public function reject(
        ContentItem $contentItem,
        string $reason
    ): ContentItem {

        try {

            return $this->contentItemRepository->update(

                $contentItem,

                [

                    'status' => 'rejected',

                    'reviewed_by' => Auth::id(),

                    'reviewed_at' => now(),

                    'rejection_reason' => $reason,

                ]

            );
        } catch (Throwable $e) {

            Log::error(

                'Content rejection failed.',

                [

                    'content_item_id' => $contentItem->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }

    /**
     * انتشار محتوا
     */
    public function publish(
        ContentItem $contentItem
    ): ContentItem {

        try {

            if ($contentItem->status !== 'approved') {

                throw new RuntimeException(
                    'Only approved content can be published.'
                );
            }

            return $this->contentItemRepository->update(

                $contentItem,

                [

                    'status' => 'published',

                    'published_at' => now(),

                ]

            );
        } catch (Throwable $e) {

            Log::error(

                'Content publish failed.',

                [

                    'content_item_id' => $contentItem->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }

    /**
     * بازگردانی به پیش نویس
     */
    public function draft(
        ContentItem $contentItem
    ): ContentItem {

        try {

            return $this->contentItemRepository->update(

                $contentItem,

                [

                    'status' => 'draft',

                    'reviewed_by' => null,

                    'reviewed_at' => null,

                    'rejection_reason' => null,

                    'published_at' => null,

                ]

            );
        } catch (Throwable $e) {

            Log::error(

                'Content draft failed.',

                [

                    'content_item_id' => $contentItem->id,

                    'error' => $e->getMessage(),

                ]

            );

            throw $e;
        }
    }
}
