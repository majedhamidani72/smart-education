<x-filament-panels::page>

    @php
        $fees = $this->getFeeData();

        // یک مثال واقعی برای این‌که عدد خشک، ملموس‌تر بشود
        $exampleAmount = 120000;

        $zibalBase = max(min($exampleAmount * $fees['zibal']['percentage'] / 100, $fees['zibal']['max']), $fees['zibal']['min']);
        $zibalVat = $zibalBase * $fees['zibal']['vat'] / 100;
        $zibalFee = (int) round($zibalBase + $zibalVat);
        $zibalNet = $exampleAmount - $zibalFee;
        $zibalTeacherShare = (int) round($zibalNet * $fees['teacher_default_percentage'] / 100);

        $bazaarFee = (int) round($exampleAmount * $fees['bazaar']['percentage'] / 100);
        $bazaarNet = $exampleAmount - $bazaarFee;
        $bazaarTeacherShare = (int) round($bazaarNet * $fees['teacher_default_percentage'] / 100);

        $myketFee = (int) round($exampleAmount * $fees['myket']['percentage'] / 100);
        $myketNet = $exampleAmount - $myketFee;
        $myketTeacherShare = (int) round($myketNet * $fees['teacher_default_percentage'] / 100);
    @endphp

    <div style="display:flex;flex-direction:column;gap:1.5rem">

        <div style="background:var(--surface-1,#fff);border:1px solid var(--border,#e5e7eb);border-radius:0.75rem;padding:1rem 1.25rem">
            <p style="font-size:0.875rem;line-height:1.8">
                این صفحه مستقیماً از «تنظیمات سیستم» خوانده می‌شود — یعنی همیشه دقیقاً همان چیزی را نشان می‌دهد که همین الان توی محاسبه‌ی درآمد معلمان استفاده می‌شود. اگر مدیریت سامانه این اعداد را تغییر بدهد، همین صفحه هم خودکار به‌روز خواهد شد.
                <br><br>
                <strong>منبع اعداد کارمزد:</strong> این ارقام بر اساس تعرفه‌های رسمی اعلام‌شده در وب‌سایت‌های خودِ هر درگاه ثبت شده‌اند (زیبال: zibal.ir، کافه‌بازار و مایکت: مستندات رسمی توسعه‌دهندگان آن‌ها). چون این تعرفه‌ها می‌توانند توسط خودِ درگاه‌ها در آینده تغییر کنند، مسئول این صفحه باید هر چند وقت یک‌بار با سایت رسمی هر درگاه تطبیق داده و در صورت نیاز از همین «تنظیمات سیستم» به‌روزرسانی شود.
            </p>
        </div>

        {{-- جدول کارمزد هر درگاه --}}
        <div style="overflow-x:auto;border:1px solid var(--border,#e5e7eb);border-radius:0.75rem">
            <table style="width:100%;border-collapse:collapse;font-size:0.875rem">
                <thead>
                    <tr style="background:var(--surface-2,#f9fafb)">
                        <th style="padding:0.75rem 1rem;text-align:right">درگاه</th>
                        <th style="padding:0.75rem 1rem;text-align:right">کارمزد پایه</th>
                        <th style="padding:0.75rem 1rem;text-align:right">کف / سقف</th>
                        <th style="padding:0.75rem 1rem;text-align:right">مالیات ارزش‌افزوده</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-top:1px solid var(--border,#e5e7eb)">
                        <td style="padding:0.75rem 1rem;font-weight:600">زیبال (سایت / اپ مستقیم)</td>
                        <td style="padding:0.75rem 1rem">{{ $fees['zibal']['percentage'] }}٪</td>
                        <td style="padding:0.75rem 1rem">{{ number_format($fees['zibal']['min']) }} تا {{ number_format($fees['zibal']['max']) }} تومان</td>
                        <td style="padding:0.75rem 1rem">{{ $fees['zibal']['vat'] }}٪ روی خودِ کارمزد</td>
                    </tr>
                    <tr style="border-top:1px solid var(--border,#e5e7eb)">
                        <td style="padding:0.75rem 1rem;font-weight:600">کافه‌بازار</td>
                        <td style="padding:0.75rem 1rem">{{ $fees['bazaar']['percentage'] }}٪</td>
                        <td style="padding:0.75rem 1rem">—</td>
                        <td style="padding:0.75rem 1rem">—</td>
                    </tr>
                    <tr style="border-top:1px solid var(--border,#e5e7eb)">
                        <td style="padding:0.75rem 1rem;font-weight:600">مایکت</td>
                        <td style="padding:0.75rem 1rem">{{ $fees['myket']['percentage'] }}٪</td>
                        <td style="padding:0.75rem 1rem">—</td>
                        <td style="padding:0.75rem 1rem">—</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- درصد پیش‌فرض سهم معلم --}}
        <div style="background:var(--surface-1,#fff);border:1px solid var(--border,#e5e7eb);border-radius:0.75rem;padding:1rem 1.25rem">
            <p style="font-size:0.875rem">
                درصد پیش‌فرض سهم معلم (روی مبلغ <strong>بعد از</strong> کسر کارمزد درگاه):
                <span style="font-weight:700;font-size:1.1rem;margin-right:0.5rem">{{ $fees['teacher_default_percentage'] }}٪</span>
            </p>
            <p style="font-size:0.75rem;color:var(--text-muted,#6b7280);margin-top:0.25rem">
                توجه: این فقط عدد پیش‌فرضِ عمومی است؛ درصد واقعی هر معلم می‌تواند در تب «کتاب‌های تدریسی» همان معلم، جدا تنظیم شده باشد.
            </p>
        </div>

        {{-- مثال محاسبه‌شده --}}
        <div style="overflow-x:auto;border:1px solid var(--border,#e5e7eb);border-radius:0.75rem">
            <table style="width:100%;border-collapse:collapse;font-size:0.875rem">
                <caption style="text-align:right;padding:0.75rem 1rem;font-weight:600">
                    مثال: برای یک خرید {{ number_format($exampleAmount) }} تومانی با درصد پیش‌فرض معلم ({{ $fees['teacher_default_percentage'] }}٪)
                </caption>
                <thead>
                    <tr style="background:var(--surface-2,#f9fafb)">
                        <th style="padding:0.75rem 1rem;text-align:right">درگاه</th>
                        <th style="padding:0.75rem 1rem;text-align:right">کارمزد کسرشده</th>
                        <th style="padding:0.75rem 1rem;text-align:right">باقی‌مانده</th>
                        <th style="padding:0.75rem 1rem;text-align:right">سهم نهایی معلم</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-top:1px solid var(--border,#e5e7eb)">
                        <td style="padding:0.75rem 1rem">زیبال</td>
                        <td style="padding:0.75rem 1rem">{{ number_format($zibalFee) }} تومان</td>
                        <td style="padding:0.75rem 1rem">{{ number_format($zibalNet) }} تومان</td>
                        <td style="padding:0.75rem 1rem;font-weight:700">{{ number_format($zibalTeacherShare) }} تومان</td>
                    </tr>
                    <tr style="border-top:1px solid var(--border,#e5e7eb)">
                        <td style="padding:0.75rem 1rem">کافه‌بازار</td>
                        <td style="padding:0.75rem 1rem">{{ number_format($bazaarFee) }} تومان</td>
                        <td style="padding:0.75rem 1rem">{{ number_format($bazaarNet) }} تومان</td>
                        <td style="padding:0.75rem 1rem;font-weight:700">{{ number_format($bazaarTeacherShare) }} تومان</td>
                    </tr>
                    <tr style="border-top:1px solid var(--border,#e5e7eb)">
                        <td style="padding:0.75rem 1rem">مایکت</td>
                        <td style="padding:0.75rem 1rem">{{ number_format($myketFee) }} تومان</td>
                        <td style="padding:0.75rem 1rem">{{ number_format($myketNet) }} تومان</td>
                        <td style="padding:0.75rem 1rem;font-weight:700">{{ number_format($myketTeacherShare) }} تومان</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</x-filament-panels::page>
