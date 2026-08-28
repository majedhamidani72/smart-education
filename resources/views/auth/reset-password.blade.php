<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>تعیین رمز عبور جدید</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="min-h-screen flex items-center justify-center p-6" style="background:radial-gradient(circle at top, #eef2ff, #f8fafc 55%)">

    <div class="w-full max-w-md">

        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-indigo-100">

            <div class="px-8 py-6" style="background:linear-gradient(135deg,#4f46e5,#7c3aed 60%)">
                <h1 class="text-white text-xl font-bold">تعیین رمز عبور جدید</h1>
                <p class="text-indigo-100 text-sm mt-1">
                    کدی که به {{ $mobile }} پیامک شد را همراه با رمز عبور جدید وارد کنید.
                </p>
            </div>

            <div class="p-8">

                @if ($errors->any())
                    <div class="mb-4 rounded-xl bg-red-50 text-red-700 text-sm p-3">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.reset.submit') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">کد تایید</label>
                        <input
                            type="text"
                            inputmode="numeric"
                            name="code"
                            dir="ltr"
                            maxlength="6"
                            placeholder="------"
                            required
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-center tracking-[0.5em] text-lg focus:border-indigo-400 focus:ring-indigo-400"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">رمز عبور جدید</label>
                        <input
                            type="password"
                            name="password"
                            dir="ltr"
                            required
                            minlength="8"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:border-indigo-400 focus:ring-indigo-400"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تکرار رمز عبور جدید</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            dir="ltr"
                            required
                            minlength="8"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 focus:border-indigo-400 focus:ring-indigo-400"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl py-2.5 font-medium text-white transition"
                        style="background:linear-gradient(135deg,#4f46e5,#7c3aed 60%)"
                    >
                        تغییر رمز عبور
                    </button>

                </form>

                <a href="{{ route('password.forgot.form') }}" class="block text-center text-sm text-gray-500 mt-6 hover:text-indigo-600">
                    کد را دریافت نکردی؟ دوباره تلاش کن
                </a>

            </div>

        </div>

    </div>

</body>

</html>
