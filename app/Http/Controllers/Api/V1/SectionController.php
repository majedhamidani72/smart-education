<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Section;
use App\Helpers\ApiResponse;
use App\Services\SectionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\SectionResource;
use App\Http\Requests\Section\StoreSectionRequest;
use App\Http\Requests\Section\UpdateSectionRequest;

class SectionController extends Controller
{
    /**
     * سرویس بخش
     */
    protected SectionService $sectionService;

    public function __construct(
        SectionService $sectionService
    ) {
        $this->sectionService = $sectionService;
    }

    /**
     * لیست بخش‌ها
     */
    public function index()
    {
        // مرور بخش‌ها آزاد است — نیازی به مجوز مدیریتی نیست.

        $sections = $this->sectionService->paginate();

        return ApiResponse::success(

            SectionResource::collection(

                $sections

            ),

            'Sections retrieved successfully.'

        );
    }

    /**
     * نمایش یک بخش
     */
    public function show(
        Section $section
    )
    {
        // مرور بخش‌ها آزاد است — نیازی به مجوز مدیریتی نیست.

        return ApiResponse::success(

            new SectionResource(

                $section

            ),

            'Section retrieved successfully.'

        );
    }

    /**
     * ایجاد بخش
     */
    public function store(
        StoreSectionRequest $request
    )
    {
        $this->authorize(
            'create',
            Section::class
        );

        $section = $this->sectionService->create(

            $request->validated()

        );

        return ApiResponse::success(

            new SectionResource(

                $section

            ),

            'Section created successfully.',

            201

        );
    }

    /**
     * بروزرسانی بخش
     */
    public function update(
        UpdateSectionRequest $request,
        Section $section
    )
    {
        $this->authorize(
            'update',
            $section
        );

        $section = $this->sectionService->update(

            $section,

            $request->validated()

        );

        return ApiResponse::success(

            new SectionResource(

                $section

            ),

            'Section updated successfully.'

        );
    }

    /**
     * حذف بخش
     */
    public function destroy(
        Section $section
    )
    {
        $this->authorize(
            'delete',
            $section
        );

        $this->sectionService->delete(

            $section

        );

        return ApiResponse::success(

            null,

            'Section deleted successfully.'

        );
    }
}
