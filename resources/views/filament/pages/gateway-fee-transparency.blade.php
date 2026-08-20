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

    <div style="display:flex;flex-direction:column;gap:1.75rem;font-size:1rem">

        {{-- توضیح و منبع --}}
        <div style="background:linear-gradient(135deg,rgba(99,102,241,0.08),rgba(20,184,166,0.06));border:1px solid rgba(99,102,241,0.25);border-radius:1rem;padding:1.5rem">
            <div style="display:flex;align-items:flex-start;gap:0.75rem;margin-bottom:0.75rem">
                <span style="font-size:1.5rem;line-height:1">ℹ️</span>
                <p style="font-size:1.05rem;line-height:2;margin:0">
                    این صفحه مستقیماً از «تنظیمات سیستم» خوانده می‌شود — یعنی همیشه دقیقاً همان چیزی را نشان می‌دهد که همین الان توی محاسبه‌ی درآمد معلمان استفاده می‌شود. اگر مدیریت سامانه این اعداد را تغییر بدهد، همین صفحه هم خودکار به‌روز خواهد شد.
                </p>
            </div>
            <div style="display:flex;align-items:flex-start;gap:0.75rem;border-top:1px solid rgba(99,102,241,0.2);padding-top:0.75rem">
                <span style="font-size:1.5rem;line-height:1">📌</span>
                <p style="font-size:1.05rem;line-height:2;margin:0">
                    <strong>منبع اعداد کارمزد:</strong> این ارقام بر اساس تعرفه‌های رسمی اعلام‌شده در وب‌سایت‌های خودِ هر درگاه ثبت شده‌اند (زیبال: zibal.ir، کافه‌بازار و مایکت: مستندات رسمی توسعه‌دهندگان آن‌ها). چون این تعرفه‌ها می‌توانند توسط خودِ درگاه‌ها در آینده تغییر کنند، مسئول این صفحه باید هر چند وقت یک‌بار با سایت رسمی هر درگاه تطبیق داده و در صورت نیاز از همین «تنظیمات سیستم» به‌روزرسانی شود.
                </p>
            </div>
        </div>

        {{-- جدول کارمزد هر درگاه --}}
        <div>
            <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:0.75rem">کارمزد هر درگاه</h2>

            <div style="overflow-x:auto;border:1px solid var(--border,#e5e7eb);border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,0.06)">
                <table style="width:100%;border-collapse:collapse;font-size:1.05rem">
                    <thead>
                        <tr style="background:rgb(99,102,241);color:#fff">
                            <th style="padding:1rem 1.25rem;text-align:right;font-weight:600">درگاه</th>
                            <th style="padding:1rem 1.25rem;text-align:right;font-weight:600">کارمزد پایه</th>
                            <th style="padding:1rem 1.25rem;text-align:right;font-weight:600">کف / سقف</th>
                            <th style="padding:1rem 1.25rem;text-align:right;font-weight:600">مالیات ارزش‌افزوده</th>
                            <th style="padding:1rem 1.25rem;text-align:right;font-weight:600">نکته</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:rgba(99,102,241,0.05)">
                            <td style="padding:1rem 1.25rem;font-weight:700">زیبال (سایت / اپ مستقیم)</td>
                            <td style="padding:1rem 1.25rem">
                                <span style="background:rgb(99,102,241);color:#fff;padding:0.25rem 0.75rem;border-radius:999px;font-weight:700;font-size:1rem">{{ $fees['zibal']['percentage'] }}٪</span>
                            </td>
                            <td style="padding:1rem 1.25rem">{{ number_format($fees['zibal']['min']) }} تا {{ number_format($fees['zibal']['max']) }} تومان</td>
                            <td style="padding:1rem 1.25rem">{{ $fees['zibal']['vat'] }}٪ روی خودِ کارمزد</td>
                            <td style="padding:1rem 1.25rem;color:var(--text-muted,#6b7280)">—</td>
                        </tr>
                        <tr style="border-top:1px solid var(--border,#e5e7eb)">
                            <td style="padding:1rem 1.25rem;font-weight:700">کافه‌بازار</td>
                            <td style="padding:1rem 1.25rem">
                                <span style="background:rgb(217,70,239);color:#fff;padding:0.25rem 0.75rem;border-radius:999px;font-weight:700;font-size:1rem">{{ $fees['bazaar']['percentage'] }}٪</span>
                            </td>
                            <td style="padding:1rem 1.25rem;color:var(--text-muted,#6b7280)">—</td>
                            <td style="padding:1rem 1.25rem;color:var(--text-muted,#6b7280)">—</td>
                            <td style="padding:1rem 1.25rem;color:var(--text-muted,#6b7280)">پلکانی: تا ۱ میلیارد تومان درآمد سالانه‌ی اپ، ۱۵٪ — بالاتر از آن، ۳۰٪</td>
                        </tr>
                        <tr style="border-top:1px solid var(--border,#e5e7eb);background:rgba(217,70,239,0.04)">
                            <td style="padding:1rem 1.25rem;font-weight:700">مایکت</td>
                            <td style="padding:1rem 1.25rem">
                                <span style="background:rgb(217,70,239);color:#fff;padding:0.25rem 0.75rem;border-radius:999px;font-weight:700;font-size:1rem">{{ $fees['myket']['percentage'] }}٪</span>
                            </td>
                            <td style="padding:1rem 1.25rem;color:var(--text-muted,#6b7280)">—</td>
                            <td style="padding:1rem 1.25rem;color:var(--text-muted,#6b7280)">—</td>
                            <td style="padding:1rem 1.25rem;color:var(--text-muted,#6b7280)">پلکانی: تا ۱ میلیارد تومان درآمد سالانه‌ی اپ، ۱۵٪ — بالاتر از آن، ۳۰٪</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- درصد پیش‌فرض سهم معلم --}}
        <div style="background:rgba(234,179,8,0.08);border:1px solid rgba(234,179,8,0.35);border-radius:1rem;padding:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
            <div>
                <p style="font-size:1rem;margin:0 0 0.4rem 0">
                    درصد پیش‌فرض سهم معلم <strong>(روی مبلغ بعد از کسر کارمزد درگاه)</strong>
                </p>
                <p style="font-size:1rem;color:var(--text-muted,#6b7280);margin:0">
                    توجه: این فقط عدد پیش‌فرضِ عمومی است؛ درصد واقعی هر معلم می‌تواند در تب «کتاب‌های تدریسی» همان معلم، جدا تنظیم شده باشد.
                </p>
            </div>
            <span style="background:rgb(234,179,8);color:#fff;font-weight:800;font-size:1.75rem;padding:0.5rem 1.25rem;border-radius:1rem;white-space:nowrap">{{ $fees['teacher_default_percentage'] }}٪</span>
        </div>

        {{-- مثال محاسبه‌شده --}}
        <div>
            <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:0.75rem">
                مثال: برای یک خرید {{ number_format($exampleAmount) }} تومانی با درصد پیش‌فرض معلم ({{ $fees['teacher_default_percentage'] }}٪)
            </h2>

            <div style="overflow-x:auto;border:1px solid var(--border,#e5e7eb);border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,0.06)">
                <table style="width:100%;border-collapse:collapse;font-size:1.05rem">
                    <thead>
                        <tr style="background:rgb(20,184,166);color:#fff">
                            <th style="padding:1rem 1.25rem;text-align:right;font-weight:600">درگاه</th>
                            <th style="padding:1rem 1.25rem;text-align:right;font-weight:600">کارمزد کسرشده</th>
                            <th style="padding:1rem 1.25rem;text-align:right;font-weight:600">باقی‌مانده</th>
                            <th style="padding:1rem 1.25rem;text-align:right;font-weight:600">سهم نهایی معلم</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:rgba(20,184,166,0.05)">
                            <td style="padding:1rem 1.25rem;font-weight:700">زیبال</td>
                            <td style="padding:1rem 1.25rem;color:#dc2626">− {{ number_format($zibalFee) }} تومان</td>
                            <td style="padding:1rem 1.25rem">{{ number_format($zibalNet) }} تومان</td>
                            <td style="padding:1rem 1.25rem;font-weight:800;color:rgb(21,128,61);font-size:1.15rem">{{ number_format($zibalTeacherShare) }} تومان</td>
                        </tr>
                        <tr style="border-top:1px solid var(--border,#e5e7eb)">
                            <td style="padding:1rem 1.25rem;font-weight:700">کافه‌بازار</td>
                            <td style="padding:1rem 1.25rem;color:#dc2626">− {{ number_format($bazaarFee) }} تومان</td>
                            <td style="padding:1rem 1.25rem">{{ number_format($bazaarNet) }} تومان</td>
                            <td style="padding:1rem 1.25rem;font-weight:800;color:rgb(21,128,61);font-size:1.15rem">{{ number_format($bazaarTeacherShare) }} تومان</td>
                        </tr>
                        <tr style="border-top:1px solid var(--border,#e5e7eb);background:rgba(20,184,166,0.05)">
                            <td style="padding:1rem 1.25rem;font-weight:700">مایکت</td>
                            <td style="padding:1rem 1.25rem;color:#dc2626">− {{ number_format($myketFee) }} تومان</td>
                            <td style="padding:1rem 1.25rem">{{ number_format($myketNet) }} تومان</td>
                            <td style="padding:1rem 1.25rem;font-weight:800;color:rgb(21,128,61);font-size:1.15rem">{{ number_format($myketTeacherShare) }} تومان</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</x-filament-panels::page>
