<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>پذیرش قوانین</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">

<div class="w-full max-w-5xl">

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

        <div class="border-b px-8 py-6">

            <h1 class="text-2xl font-bold text-slate-800">

                قوانین همکاری

            </h1>

            <p class="text-sm text-slate-500 mt-2">

                لطفاً متن زیر را با دقت مطالعه کنید.
                برای ادامه استفاده از پنل، پذیرش قوانین الزامی است.

            </p>

        </div>



        <div
            id="agreementBox"
            class="h-[450px] overflow-y-auto p-8 leading-9 text-slate-700 whitespace-pre-line">

            {!! $agreementText !!}

        </div>



        <div class="border-t bg-slate-50 px-8 py-6">

            <div class="flex items-center gap-2 mb-6">

                <input

                    id="accept"

                    type="checkbox"

                    disabled

                    class="w-5 h-5"

                >

                <label
                    for="accept"
                    class="text-sm">

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

                    class="w-full rounded-xl bg-indigo-600 py-3 text-white font-bold disabled:bg-slate-400 disabled:cursor-not-allowed hover:bg-indigo-700 transition"

                >

                    ورود به پنل

                </button>

            </form>



            <div class="mt-6 text-xs text-slate-500 flex justify-between">

                <span>

                    نوع قوانین :

                    {{ $agreementType }}

                </span>

                <span>

                    نسخه :

                    {{ $agreementVersion }}

                </span>

            </div>

        </div>

    </div>

</div>

<script>

const box = document.getElementById('agreementBox');

const check = document.getElementById('accept');

const button = document.getElementById('submitButton');

box.addEventListener('scroll', () => {

    const end =

        box.scrollTop + box.clientHeight >=

        box.scrollHeight - 10;

    if (end) {

        check.disabled = false;

    }

});

check.addEventListener('change', () => {

    button.disabled = !check.checked;

});

</script>

</body>

</html>
