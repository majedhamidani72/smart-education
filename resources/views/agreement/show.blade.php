<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>پذیرش قوانین</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .agreement-card { animation: fadeInUp 0.4s ease-out; }

        #agreementBox { scrollbar-width: thin; scrollbar-color: #a5b4fc #f1f5f9; }
        #agreementBox::-webkit-scrollbar { width: 8px; }
        #agreementBox::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 999px; }
        #agreementBox::-webkit-scrollbar-thumb { background: #a5b4fc; border-radius: 999px; }
    </style>

</head>

<body class="min-h-screen flex items-center justify-center p-6" style="background:radial-gradient(circle at top, #eef2ff, #f8fafc 55%)">

<div class="w-full max-w-5xl agreement-card">

    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-indigo-100">

        <div class="px-8 py-8" style="background:linear-gradient(135deg,#4f46e5,#7c3aed 60%,#ec4899)">

            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-3xl shrink-0">
                    📜
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-white">
                        قوانین همکاری
                    </h1>
                    <p class="text-sm text-indigo-100 mt-1">
                        لطفاً متن زیر را با دقت تا انتها مطالعه کنید — پذیرش آن برای ادامه‌ی استفاده از پنل الزامی است.
                    </p>
                </div>
            </div>

            <div class="flex gap-2 mt-5">
                <span class="inline-flex items-center gap-1 text-xs font-semibold bg-white/15 text-white px-3 py-1.5 rounded-full">
                    نوع: {{ $agreementType }}
                </span>
                <span class="inline-flex items-center gap-1 text-xs font-semibold bg-white/15 text-white px-3 py-1.5 rounded-full">
                    نسخه: {{ $agreementVersion }}
                </span>
            </div>

        </div>

        <div class="h-1.5 bg-slate-100">
            <div id="progressBar" class="h-full bg-gradient-to-l from-indigo-500 via-purple-500 to-pink-500 transition-all duration-150" style="width:0%"></div>
        </div>

        <div
            id="agreementBox"
            class="h-[450px] overflow-y-auto px-8 py-8 leading-9 text-slate-700 whitespace-pre-line text-[15px]">

            {!! $agreementText !!}

        </div>

        <div class="border-t border-indigo-100 px-8 py-7" style="background:linear-gradient(180deg,#f8fafc,#f1f5f9)">

            <div id="scrollHint" class="flex items-center gap-2 mb-5 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 w-fit">
                <span>⬇️</span>
                <span>تا پایین متن اسکرول کن تا بتونی قبولش کنی</span>
            </div>

            <div id="acceptedHint" class="hidden items-center gap-2 mb-5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2.5 w-fit">
                <span>✅</span>
                <span>متن رو کامل خوندی — می‌تونی قبولش کنی</span>
            </div>

            <div class="flex items-center gap-3 mb-6">

                <input
                    id="accept"
                    type="checkbox"
                    disabled
                    class="w-5 h-5 rounded accent-indigo-600 disabled:opacity-40"
                >

                <label
                    for="accept"
                    class="text-sm font-medium text-slate-700">

                    تمامی قوانین فوق را مطالعه کرده و می‌پذیرم.

                </label>

            </div>

            <form
                method="POST"
                action="{{ route('agreement.accept') }}">

                @csrf

                <input
                    type="hidden"
                    name="accept"
                    value="1">

                <button
                    id="submitButton"
                    disabled
                    class="w-full rounded-2xl py-3.5 text-white font-bold text-[15px] shadow-lg shadow-indigo-200 transition disabled:shadow-none"
                    style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"
                >

                    ورود به پنل ←

                </button>

            </form>

        </div>

    </div>

</div>

<style>
    #submitButton:disabled { background: #cbd5e1 !important; box-shadow: none !important; }
</style>

<script>

const box = document.getElementById('agreementBox');

const check = document.getElementById('accept');

const button = document.getElementById('submitButton');

const progressBar = document.getElementById('progressBar');

const scrollHint = document.getElementById('scrollHint');

const acceptedHint = document.getElementById('acceptedHint');

box.addEventListener('scroll', () => {

    const percent = Math.min(
        100,
        Math.round((box.scrollTop / (box.scrollHeight - box.clientHeight)) * 100)
    );

    progressBar.style.width = percent + '%';

    const end =
        box.scrollTop + box.clientHeight >=
        box.scrollHeight - 10;

    if (end) {

        check.disabled = false;

        scrollHint.classList.add('hidden');

        acceptedHint.classList.remove('hidden');

        acceptedHint.classList.add('flex');
    }

});

check.addEventListener('change', () => {

    button.disabled = !check.checked;

});

</script>

</body>

</html>
