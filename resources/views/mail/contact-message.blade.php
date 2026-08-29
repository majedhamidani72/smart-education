<!doctype html>
<html lang="fa" dir="rtl">
<head><meta charset="utf-8"><title>پیام جدید سایت درسکا</title></head>
<body style="margin:0;background:#f8fafc;font-family:Tahoma,Arial,sans-serif;color:#1e293b">
    <div style="max-width:640px;margin:32px auto;background:#fff;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden">
        <div style="padding:22px 28px;background:linear-gradient(135deg,#f97316,#f43f5e);color:#fff">
            <h1 style="margin:0;font-size:21px">پیام جدید از فرم تماس درسکا</h1>
        </div>
        <div style="padding:26px 28px;line-height:2">
            <p><strong>نام:</strong> {{ $contact['name'] }}</p>
            <p><strong>شماره موبایل:</strong> <span dir="ltr">{{ $contact['mobile'] }}</span></p>
            <p><strong>ایمیل:</strong> <span dir="ltr">{{ $contact['email'] ?: 'وارد نشده' }}</span></p>
            <p><strong>موضوع:</strong> {{ $contact['subject'] }}</p>
            <div style="margin-top:20px;padding:18px;background:#fff7ed;border-radius:12px;white-space:pre-wrap">{{ $contact['message'] }}</div>
        </div>
    </div>
</body>
</html>
