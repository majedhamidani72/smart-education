<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    public function index(Request $request)
    {
        $position = $request->validate(['position' => ['required', 'in:home,book,lesson,quiz,profile']])['position'];
        $items = Advertisement::query()->where('position', $position)->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->orderBy('sort_order')->limit(4)->get()->map(fn (Advertisement $ad) => [
                'id' => $ad->id,
                'title' => $ad->title,
                'image' => Storage::disk('public')->url($ad->image),
                'link' => $ad->link,
            ]);

        return ApiResponse::success(['items' => $items], 'تبلیغات فعال دریافت شد.');
    }

    public function view(Request $request, Advertisement $advertisement)
    {
        abort_unless($advertisement->isActive(), 404);
        $identity = $request->user('sanctum')?->id ?: $request->ip();
        $key = 'ad-view:'.$advertisement->id.':'.sha1((string) $identity);
        if (Cache::add($key, true, now()->addMinutes(30))) {
            $advertisement->views()->create($this->trackingData($request));
        }
        return ApiResponse::success(['recorded' => true]);
    }

    public function click(Request $request, Advertisement $advertisement)
    {
        abort_unless($advertisement->isActive(), 404);
        $advertisement->clicks()->create($this->trackingData($request) + ['referer' => $request->header('referer')]);
        return ApiResponse::success(['url' => $advertisement->link]);
    }

    private function trackingData(Request $request): array
    {
        $agent = (string) $request->userAgent();
        return [
            'user_id' => $request->user('sanctum')?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $agent,
            'device_type' => preg_match('/Mobile|Android|iPhone/i', $agent) ? 'mobile' : 'desktop',
            'platform' => preg_match('/Windows/i', $agent) ? 'windows' : (preg_match('/Android/i', $agent) ? 'android' : 'other'),
        ];
    }
}
