<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>فراموشی رمز عبور</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="min-h-screen flex items-center justify-center p-6" style="background:radial-gradient(circle at top, #eef2ff, #f8fafc 55%)">

    <div class="w-full max-w-md">

        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-indigo-100">

            <div class="px-8 py-6" style="background:linear-gradient(135deg,#4f46e5,#7c3aed 60%)">
                <h1 class="text-white text-xl font-bold">فراموشی رمز عبور</h1>
                <p class="text-indigo-100 text-sm mt-1">شماره موبایلی که با آن ثبت‌نام کرده‌اید را وارد کنید.</p>
            </div>

            <div class="p-8">

                @if (session('status'))
                    <div class="mb-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm p-3">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl bg-red-50 text-red-700 text-sm p-3">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.forgot.send') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">شماره موبایل</label>
                        <input
                            type="tel"
                            name="mobile"
                            dir="ltr"
                            placeholder="09123456789"
                            value="{{ old('mobile') }}"
                            required
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-center focus:border-indigo-400 focus:ring-indigo-400"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl py-2.5 font-medium text-white transition"
                        style="background:linear-gradient(135deg,#4f46e5,#7c3aed 60%)"
                    >
                        دریافت کد تایید
                    </button>

                </form>

                <a href="{{ route('filament.admin.auth.login') }}" class="block text-center text-sm text-gray-500 mt-6 hover:text-indigo-600">
                    بازگشت به صفحه‌ی ورود
                </a>

            </div>

        </div>

    </div>

</body>

</html>
