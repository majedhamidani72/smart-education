<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\ContentItem;
use App\Models\ContentType;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'grade_id' => ['nullable', 'integer', 'exists:grades,id'],
            'book_id' => ['nullable', 'integer', 'exists:books,id'],
            'chapter_id' => ['nullable', 'integer', 'exists:chapters,id'],
            'content_type_id' => ['nullable', 'integer', 'exists:content_types,id'],
            'accessible_only' => ['nullable', 'boolean'],
        ]);

        $term = trim($data['q'] ?? '');
        $user = auth('sanctum')->user();

        $items = ContentItem::query()
            ->where('status', 'approved')
            ->with(['contentType', 'section.chapter.book.appGradeSubject.grade', 'chapter.book.appGradeSubject.grade'])
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('page_number', 'like', "%{$term}%")
                        ->orWhereHas('section', fn ($x) => $x->where('title', 'like', "%{$term}%"))
                        ->orWhereHas('chapter', fn ($x) => $x->where('title', 'like', "%{$term}%"))
                        ->orWhereHas('chapter.book', fn ($x) => $x->where('title', 'like', "%{$term}%"))
                        ->orWhereHas('section.chapter.book', fn ($x) => $x->where('title', 'like', "%{$term}%"));
                });
            })
            ->when($data['grade_id'] ?? null, fn ($q, $id) => $q->where(fn ($x) => $x
                ->whereHas('chapter.book.appGradeSubject', fn ($y) => $y->where('grade_id', $id))
                ->orWhereHas('section.chapter.book.appGradeSubject', fn ($y) => $y->where('grade_id', $id))))
            ->when($data['book_id'] ?? null, fn ($q, $id) => $q->where(fn ($x) => $x
                ->whereHas('chapter', fn ($y) => $y->where('book_id', $id))
                ->orWhereHas('section.chapter', fn ($y) => $y->where('book_id', $id))))
            ->when($data['chapter_id'] ?? null, fn ($q, $id) => $q->where(fn ($x) => $x
                ->where('chapter_id', $id)->orWhereHas('section', fn ($y) => $y->where('chapter_id', $id))))
            ->when($data['content_type_id'] ?? null, fn ($q, $id) => $q->where('content_type_id', $id))
            ->orderBy('sort_order')->limit(60)->get();

        $results = $items->map(function (ContentItem $item) use ($user) {
            $chapter = $item->chapter ?? $item->section?->chapter;
            $book = $chapter?->book;
            $hasAccess = $item->is_free || ($user && $user->hasAccessToContentItem($item));
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'page_number' => $item->page_number,
                'thumbnail' => $item->thumbnail ? Storage::disk('public')->url($item->thumbnail) : null,
                'is_free' => $item->is_free,
                'has_access' => (bool) $hasAccess,
                'type' => $item->contentType ? ['id' => $item->contentType->id, 'title' => $item->contentType->title, 'slug' => $item->contentType->slug] : null,
                'grade' => $book?->appGradeSubject?->grade ? ['id' => $book->appGradeSubject->grade->id, 'title' => $book->appGradeSubject->grade->title] : null,
                'book' => $book ? ['id' => $book->id, 'title' => $book->title] : null,
                'chapter' => $chapter ? ['id' => $chapter->id, 'title' => $chapter->title] : null,
                'section' => $item->section ? ['id' => $item->section->id, 'title' => $item->section->title] : null,
            ];
        });

        if (($data['accessible_only'] ?? false) && $user) {
            $results = $results->where('has_access', true)->values();
        }

        $bookQuery = Book::query()->where('is_active', true)->with('appGradeSubject.grade')->orderBy('sort_order');
        if ($data['grade_id'] ?? null) {
            $bookQuery->whereHas('appGradeSubject', fn ($q) => $q->where('grade_id', $data['grade_id']));
        }
        $chapterQuery = Chapter::query()->where('is_active', true)->orderBy('sort_order');
        if ($data['book_id'] ?? null) $chapterQuery->where('book_id', $data['book_id']);
        else $chapterQuery->whereRaw('1 = 0');

        return ApiResponse::success([
            'results' => $results,
            'count' => $results->count(),
            'filters' => [
                'grades' => Grade::query()->orderBy('grade_number')->get(['id', 'title']),
                'books' => $bookQuery->get()->map(fn ($book) => ['id' => $book->id, 'title' => $book->title, 'grade_id' => $book->appGradeSubject?->grade_id]),
                'chapters' => $chapterQuery->get(['id', 'book_id', 'title']),
                'content_types' => ContentType::query()->orderBy('sort_order')->get(['id', 'title', 'slug']),
            ],
        ], 'نتایج جست‌وجو دریافت شد.');
    }
}
