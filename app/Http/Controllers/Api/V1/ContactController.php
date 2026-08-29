<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Mail\ContactMessageMail;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class ContactController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'email' => ['nullable', 'email:rfc', 'max:150'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'name.required' => 'نام و نام خانوادگی را وارد کنید.',
            'mobile.required' => 'شماره موبایل را وارد کنید.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'email.email' => 'نشانی ایمیل معتبر نیست.',
            'subject.required' => 'موضوع پیام را وارد کنید.',
            'message.required' => 'متن پیام را وارد کنید.',
            'message.min' => 'متن پیام باید حداقل ۱۰ نویسه باشد.',
        ]);

        $recipient = trim((string) Setting::getValue(
            'contact_email',
            config('mail.from.address')
        ));

        if ($recipient === '' || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('ایمیل دریافت پیام‌ها در تنظیمات سیستم معتبر نیست.');
        }

        if (config('mail.default') === 'log') {
            throw new RuntimeException('سرویس ارسال ایمیل هنوز روی سرور تنظیم نشده است.');
        }

        Mail::to($recipient)->send(new ContactMessageMail($data));

        return ApiResponse::success(
            null,
            'پیام شما با موفقیت ارسال شد. در اولین فرصت با شما تماس می‌گیریم.'
        );
    }
}
