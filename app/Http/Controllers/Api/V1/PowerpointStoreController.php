<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Powerpoint;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PowerpointStoreController extends Controller
{
    public function index(Request $request)
    {
        $query = Powerpoint::query()->where('is_active', true)
            ->with(['app', 'grade', 'book', 'chapter'])->orderBy('sort_order');
        $query->when($request->integer('grade_id'), fn ($q, $id) => $q->where('grade_id', $id));
        $query->when($request->integer('book_id'), fn ($q, $id) => $q->where('book_id', $id));
        $query->when($request->integer('chapter_id'), fn ($q, $id) => $q->where('chapter_id', $id));
        $user = auth('sanctum')->user();

        $items = $query->get()->map(function (Powerpoint $item) use ($user) {
            $owned = $user ? $this->ownedBy($item, $user->id) : false;
            return [
                'id' => $item->id, 'title' => $item->title, 'description' => $item->description,
                'preview_image' => $item->preview_image ? Storage::disk('public')->url($item->preview_image) : null,
                'price' => $item->price, 'discount_price' => $item->discount_price,
                'final_price' => $item->finalPrice(), 'discount_percent' => $item->discount_price
                    ? (int) round(($item->price - $item->discount_price) * 100 / max(1, $item->price)) : 0,
                'slides_count' => $item->slides_count, 'owned' => $owned,
                'app' => ['id' => $item->app_id, 'title' => $item->app?->title],
                'grade' => ['id' => $item->grade_id, 'title' => $item->grade?->title],
                'book' => ['id' => $item->book_id, 'title' => $item->book?->title],
                'chapter' => ['id' => $item->chapter_id, 'title' => $item->chapter?->title],
            ];
        });

        return ApiResponse::success(['items' => $items, 'count' => $items->count()], 'فروشگاه پاورپوینت دریافت شد.');
    }

    public function purchase(Request $request, Powerpoint $powerpoint)
    {
        abort_unless($powerpoint->is_active, 404);
        $data = $request->validate(['accepted_license' => ['accepted']]);
        if ($this->ownedBy($powerpoint, $request->user()->id)) {
            return ApiResponse::error('این پاورپوینت قبلاً خریداری شده است.', null, 422);
        }

        $purchase = DB::transaction(function () use ($request, $powerpoint) {
            $purchase = Purchase::create([
                'user_id' => $request->user()->id,
                'invoice_number' => 'PPT-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5)),
                'total_amount' => $powerpoint->price,
                'discount_amount' => $powerpoint->price - $powerpoint->finalPrice(),
                'payable_amount' => $powerpoint->finalPrice(),
                'status' => 'pending',
                'notes' => 'مجوز استفاده کلاسی پاورپوینت توسط خریدار پذیرفته شد.',
            ]);
            $purchase->items()->create([
                'plan_id' => null, 'item_type' => 'powerpoint', 'item_id' => $powerpoint->id,
                'title' => $powerpoint->title, 'price' => $powerpoint->price,
                'discount_amount' => $powerpoint->price - $powerpoint->finalPrice(),
                'final_price' => $powerpoint->finalPrice(), 'quantity' => 1,
                'notes' => 'حق استفاده صرفاً در کلاس حضوری خریدار',
            ]);
            return $purchase->load('items');
        });

        return ApiResponse::success(['purchase_id' => $purchase->id, 'invoice_number' => $purchase->invoice_number, 'payable_amount' => $purchase->payable_amount], 'سفارش پاورپوینت ایجاد شد.', 201);
    }

    public function orders(Request $request)
    {
        $orders = Purchase::query()
            ->where('user_id', $request->user()->id)
            ->whereHas('items', fn ($query) => $query->where('item_type', 'powerpoint'))
            ->with(['items' => fn ($query) => $query->where('item_type', 'powerpoint')])
            ->latest()->get()->map(fn (Purchase $purchase) => [
                'id' => $purchase->id,
                'invoice_number' => $purchase->invoice_number,
                'status' => $purchase->status,
                'payable_amount' => $purchase->payable_amount,
                'created_at' => $purchase->created_at?->toIso8601String(),
                'powerpoint' => [
                    'id' => $purchase->items->first()?->item_id,
                    'title' => $purchase->items->first()?->title,
                ],
            ]);

        return ApiResponse::success(['items' => $orders], 'سفارش‌های پاورپوینت دریافت شد.');
    }

    public function cancel(Request $request, Purchase $purchase)
    {
        abort_unless($purchase->user_id === $request->user()->id, 403);
        abort_unless($purchase->status === 'pending' && $purchase->items()->where('item_type', 'powerpoint')->exists(), 422, 'این سفارش قابل لغو نیست.');
        $purchase->update(['status' => 'cancelled']);
        return ApiResponse::success(['cancelled' => true], 'سفارش لغو شد.');
    }

    public function download(Request $request, Powerpoint $powerpoint)
    {
        abort_unless($this->ownedBy($powerpoint, $request->user()->id), 403, 'ابتدا این پاورپوینت را خریداری کنید.');
        abort_unless(Storage::disk('local')->exists($powerpoint->file_path), 404, 'فایل پاورپوینت پیدا نشد.');
        $extension = pathinfo($powerpoint->file_path, PATHINFO_EXTENSION) ?: 'pptx';
        $safeName = Str::slug($powerpoint->title) ?: 'powerpoint-'.$powerpoint->id;
        return Storage::disk('local')->download($powerpoint->file_path, $safeName.'.'.$extension);
    }

    private function ownedBy(Powerpoint $powerpoint, int $userId): bool
    {
        return Purchase::query()->where('user_id', $userId)->where('status', 'paid')
            ->whereHas('items', fn ($q) => $q->where('item_type', 'powerpoint')->where('item_id', $powerpoint->id))->exists();
    }
}
