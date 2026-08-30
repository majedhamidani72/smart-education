<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>قرارداد {{ $agreement->teacher?->name }} — درسکا</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Tahoma, "Segoe UI", sans-serif;
            color: #1e293b;
            background: #f1f5f9;
            margin: 0;
            padding: 24px;
        }

        .sheet {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0 0 4px;
            color: #4f46e5;
        }

        .header .subtitle {
            font-size: 13px;
            color: #64748b;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 12px;
            background: #eef2ff;
            color: #4338ca;
            font-weight: bold;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 24px;
            margin-bottom: 28px;
            font-size: 13px;
        }

        .meta-grid dt {
            color: #64748b;
            margin-bottom: 2px;
        }

        .meta-grid dd {
            margin: 0;
            font-weight: bold;
        }

        .agreement-text {
            white-space: pre-wrap;
            line-height: 2;
            font-size: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            background: #f8fafc;
            margin-bottom: 24px;
        }

        .no-text-notice {
            font-size: 13px;
            color: #b45309;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 24px;
        }

        .footer-note {
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 14px;
            margin-top: 24px;
        }

        .print-bar {
            max-width: 800px;
            margin: 0 auto 16px;
            text-align: left;
        }

        .print-bar button {
            background: #4f46e5;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            cursor: pointer;
        }

        @media print {

            body {
                background: #fff;
                padding: 0;
            }

            .print-bar {
                display: none;
            }

            .sheet {
                border: none;
                border-radius: 0;
                max-width: none;
            }

        }

    </style>

</head>

<body>

    <div class="print-bar">
        <button onclick="window.print()">چاپ / ذخیره PDF</button>
    </div>

    <div class="sheet">

        <div class="header">
            <div>
                <h1>سند پذیرش قرارداد همکاری — درسکا</h1>
                <div class="subtitle">
                    این سند به‌صورت خودکار از سامانه‌ی درسکا تولید شده است.
                </div>
            </div>

            <span class="badge">
                {{ $agreement->agreement_type === 'admin' ? 'قرارداد ادمین' : 'قرارداد معلم' }}
            </span>
        </div>

        <dl class="meta-grid">

            <div>
                <dt>نام و نام خانوادگی</dt>
                <dd>{{ $agreement->teacher?->name ?? '—' }}</dd>
            </div>

            <div>
                <dt>شماره موبایل</dt>
                <dd>{{ $agreement->teacher?->mobile ?? '—' }}</dd>
            </div>

            <div>
                <dt>نسخه‌ی قرارداد</dt>
                <dd>{{ $agreement->agreement_version }}</dd>
            </div>

            <div>
                <dt>تاریخ پذیرش</dt>
                <dd>{{ \App\Support\Jalali::format($agreement->accepted_at) }}</dd>
            </div>

            <div>
                <dt>آدرس IP</dt>
                <dd>{{ $agreement->ip_address ?? '—' }}</dd>
            </div>

            <div>
                <dt>دستگاه / مرورگر</dt>
                <dd style="font-weight: normal; font-size: 11px; word-break: break-all;">
                    {{ $agreement->user_agent ?? '—' }}
                </dd>
            </div>

        </dl>

        @if ($agreement->agreement_text)

            <div class="agreement-text">{{ $agreement->agreement_text }}</div>

        @else

            <div class="no-text-notice">
                متن دقیق این قرارداد در لحظه‌ی پذیرش ذخیره نشده بود (این پذیرش مربوط به قبل از
                فعال‌سازی قابلیت ذخیره‌ی متن است). نسخه‌ی پذیرفته‌شده «{{ $agreement->agreement_version }}»
                بوده که ممکن است با متن فعلی قرارداد در تنظیمات یکسان نباشد.
            </div>

        @endif

        <div class="footer-note">
            کد پیگیری سند: #{{ $agreement->id }} — تولید شده در {{ \App\Support\Jalali::format(now()) }}
        </div>

    </div>

</body>

</html>
