<x-filament-panels::page>

    @php
        $palette = [
            ['bg' => 'rgba(99,102,241,0.06)', 'accent' => 'rgb(99,102,241)'],
            ['bg' => 'rgba(20,184,166,0.06)', 'accent' => 'rgb(20,184,166)'],
            ['bg' => 'rgba(234,179,8,0.06)', 'accent' => 'rgb(202,138,4)'],
            ['bg' => 'rgba(217,70,239,0.06)', 'accent' => 'rgb(217,70,239)'],
            ['bg' => 'rgba(239,68,68,0.06)', 'accent' => 'rgb(220,38,38)'],
        ];
        $isReviewer = $this->isReviewer();
    @endphp

    {{-- سطح ۱: اپلیکیشن / ایجادکننده / پایه / کتاب --}}
    @if ($viewLevel === 'groups')

        @php $groups = $this->getGroups(); @endphp

        @if ($groups->isEmpty())
            <div style="text-align:center;padding:3rem;color:var(--text-muted,#6b7280)">
                هنوز هیچ سوالی با محتوای مشخص ثبت نشده است.
            </div>
        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem">
                @foreach ($groups as $i => $group)
                    @php $colors = $palette[$i % count($palette)]; @endphp
                    <button
                        wire:click="selectGroup({{ $group['app_id'] }}, {{ $group['creator_id'] }}, {{ $group['grade_id'] }}, {{ $group['book_id'] }})"
                        style="position:relative;text-align:right;cursor:pointer;background:{{ $colors['bg'] }};border:1px solid {{ $colors['accent'] }}33;border-right:4px solid {{ $colors['accent'] }};border-radius:1rem;padding:1.1rem 1.3rem;transition:transform .15s"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'">

                        @if ($isReviewer && $group['pending_count'] > 0)
                            <span style="position:absolute;top:-.5rem;left:-.5rem;background:rgb(220,38,38);color:#fff;font-size:.7rem;font-weight:700;border-radius:999px;padding:.15rem .5rem">
                                {{ $group['pending_count'] }} در انتظار
                            </span>
                        @endif

                        <div style="font-weight:700;font-size:1rem;margin-bottom:.5rem;color:{{ $colors['accent'] }}">
                            📱 اپلیکیشن: {{ $group['app_title'] }}
                        </div>
                        <div style="font-size:.85rem;color:var(--text-secondary,#4b5563);line-height:1.9">
                            👤 ایجادکننده: {{ $group['creator_title'] }}<br>
                            🎓 پایه: {{ $group['grade_title'] }}<br>
                            📗 کتاب: {{ $group['book_title'] }}
                        </div>
                        <div style="margin-top:.6rem;font-size:.75rem;font-weight:600;color:{{ $colors['accent'] }}">
                            {{ $group['count'] }} سوال ←
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

    {{-- سطح ۲: بخش / فصل / کل کتاب --}}
    @elseif ($viewLevel === 'examLevels')

        @php $counts = $this->getExamLevelCounts(); @endphp

        <button wire:click="backToGroups" style="margin-bottom:1.25rem;font-size:.85rem;color:var(--text-muted,#6b7280);background:none;border:none;cursor:pointer">
            → بازگشت به لیست کتاب‌ها
        </button>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1rem">

            <button wire:click="selectExamLevel('section')"
                style="text-align:right;cursor:pointer;background:rgba(20,184,166,0.06);border:1px solid rgba(20,184,166,0.3);border-right:4px solid rgb(20,184,166);border-radius:1rem;padding:1.25rem">
                <div style="font-weight:700;color:rgb(13,148,136)">📘 آزمون‌های بخش</div>
                <div style="font-size:1.5rem;font-weight:800;margin-top:.5rem">{{ $counts['section'] }}</div>
                <div style="font-size:.75rem;color:var(--text-muted,#6b7280)">سوال</div>
            </button>

            <button wire:click="selectExamLevel('chapter')"
                style="text-align:right;cursor:pointer;background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.3);border-right:4px solid rgb(99,102,241);border-radius:1rem;padding:1.25rem">
                <div style="font-weight:700;color:rgb(79,70,229)">📙 آزمون‌های فصل</div>
                <div style="font-size:1.5rem;font-weight:800;margin-top:.5rem">{{ $counts['chapter'] }}</div>
                <div style="font-size:.75rem;color:var(--text-muted,#6b7280)">سوال</div>
            </button>

            <button wire:click="selectExamLevel('book')"
                style="text-align:right;cursor:pointer;background:rgba(234,179,8,0.06);border:1px solid rgba(234,179,8,0.3);border-right:4px solid rgb(202,138,4);border-radius:1rem;padding:1.25rem">
                <div style="font-weight:700;color:rgb(161,98,7)">📕 آزمون‌های کل کتاب</div>
                <div style="font-size:1.5rem;font-weight:800;margin-top:.5rem">{{ $counts['book'] }}</div>
                <div style="font-size:.7rem;color:var(--text-muted,#6b7280)">هنوز پشتیبانی نمی‌شود</div>
            </button>

        </div>

    {{-- سطح ۳: لیست واقعی سوالات، به تفکیک فصل/بخش و قابل‌جمع‌شدن --}}
    @else

        @php $grouped = $this->getFilteredQuestionsGrouped(); @endphp

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">

            <button wire:click="backToExamLevels" style="font-size:.85rem;color:var(--text-muted,#6b7280);background:none;border:none;cursor:pointer">
                → بازگشت
            </button>

            <a href="{{ \App\Filament\Pages\AddQuestionsToBank::getUrl(['book_id' => $selectedBookId]) }}"
               style="background:rgb(99,102,241);color:#fff;font-weight:600;font-size:.85rem;padding:.55rem 1.1rem;border-radius:.7rem;text-decoration:none">
                + ایجاد سوال جدید در این کتاب
            </a>

        </div>

        @if ($grouped->isEmpty())
            <div style="text-align:center;padding:3rem;color:var(--text-muted,#6b7280)">
                سوالی در این دسته یافت نشد.
            </div>
        @else

            @php
                // رنگ هر فصل بر اساس هش شناسه‌ی همان فصل ثابت
                // می‌ماند — یعنی همیشه یک فصل مشخص، همیشه همان رنگ
                // را دارد (نه رنگ تصادفی هر بار که صفحه باز می‌شود).
                $chapterPalette = [
                    ['bg' => 'rgba(99,102,241,0.07)', 'accent' => 'rgb(99,102,241)'],
                    ['bg' => 'rgba(20,184,166,0.07)', 'accent' => 'rgb(13,148,136)'],
                    ['bg' => 'rgba(234,179,8,0.07)', 'accent' => 'rgb(202,138,4)'],
                    ['bg' => 'rgba(217,70,239,0.07)', 'accent' => 'rgb(192,38,211)'],
                    ['bg' => 'rgba(239,68,68,0.07)', 'accent' => 'rgb(220,38,38)'],
                    ['bg' => 'rgba(34,197,94,0.07)', 'accent' => 'rgb(21,128,61)'],
                    ['bg' => 'rgba(59,130,246,0.07)', 'accent' => 'rgb(37,99,235)'],
                    ['bg' => 'rgba(249,115,22,0.07)', 'accent' => 'rgb(194,65,12)'],
                ];
            @endphp

            {{-- هر زیرگروه یک <details> جمع‌شونده است — با تعداد
                 زیاد بخش، صفحه شلوغ نمی‌شود چون فقط عنوان‌ها دیده
                 می‌شوند تا خودت یکی را باز کنی. رنگ هر فصل با
                 فصل‌های دیگر فرق دارد تا سریع از هم تشخیص داده
                 شوند. --}}
            <div style="display:flex;flex-direction:column;gap:.85rem">
                @foreach ($grouped as $subGroup)

                    @php
                        $chapterColors = $chapterPalette[crc32((string) $subGroup['chapter_id']) % count($chapterPalette)];
                    @endphp

                    <details style="border:1px solid {{ $chapterColors['accent'] }}33;border-right:4px solid {{ $chapterColors['accent'] }};border-radius:1rem;overflow:hidden">

                        <summary style="cursor:pointer;list-style:none;background:{{ $chapterColors['bg'] }};padding:.85rem 1.1rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem">

                            <div style="font-weight:700;font-size:.9rem;color:{{ $chapterColors['accent'] }}">
                                @if ($subGroup['section_title'])
                                    فصل: {{ $subGroup['chapter_title'] }} — بخش: {{ $subGroup['section_title'] }}
                                @elseif ($subGroup['chapter_title'])
                                    فصل: {{ $subGroup['chapter_title'] }}
                                @else
                                    کل کتاب (بدون فصل مشخص)
                                @endif
                                <span style="font-weight:400;color:var(--text-muted,#6b7280);font-size:.8rem">({{ $subGroup['questions']->count() }} سوال)</span>

                                @if ($isReviewer && $subGroup['pending_count'] > 0)
                                    <span style="background:rgb(202,138,4);color:#fff;font-size:.7rem;font-weight:700;border-radius:999px;padding:.15rem .55rem;margin-right:.4rem">
                                        {{ $subGroup['pending_count'] }} در انتظار بررسی
                                    </span>
                                @endif
                            </div>

                            <div style="display:flex;gap:.5rem;flex-wrap:wrap" onclick="event.stopPropagation()">

                                @if ($isReviewer && $subGroup['pending_count'] > 0)
                                    <button
                                        wire:click="bulkReviewGroup({{ $subGroup['chapter_id'] ?? 'null' }}, {{ $subGroup['section_id'] ?? 'null' }}, 'approve')"
                                        wire:confirm="همه‌ی {{ $subGroup['pending_count'] }} سوال در انتظار این قسمت تأیید شوند؟"
                                        style="background:rgb(22,163,74);color:#fff;font-weight:600;font-size:.8rem;padding:.4rem .9rem;border-radius:.6rem;border:none;cursor:pointer">
                                        ✅ تأیید همه
                                    </button>
                                    <button
                                        wire:click="bulkReviewGroup({{ $subGroup['chapter_id'] ?? 'null' }}, {{ $subGroup['section_id'] ?? 'null' }}, 'reject')"
                                        wire:confirm="همه‌ی {{ $subGroup['pending_count'] }} سوال در انتظار این قسمت رد شوند؟"
                                        style="background:rgb(220,38,38);color:#fff;font-weight:600;font-size:.8rem;padding:.4rem .9rem;border-radius:.6rem;border:none;cursor:pointer">
                                        ❌ رد همه
                                    </button>
                                @endif

                                <a href="{{ \App\Filament\Pages\AddQuestionsToBank::getUrl([
                                        'book_id' => $selectedBookId,
                                        'chapter_id' => $subGroup['chapter_id'],
                                        'section_id' => $subGroup['section_id'],
                                    ]) }}"
                                   style="background:rgb(20,184,166);color:#fff;font-weight:600;font-size:.8rem;padding:.4rem .9rem;border-radius:.6rem;text-decoration:none;white-space:nowrap">
                                    ادامه‌ی افزودن سوال ←
                                </a>

                            </div>

                        </summary>

                        <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                            <thead>
                                <tr style="background:var(--surface-2,#f9fafb);border-top:1px solid var(--border,#e5e7eb)">
                                    <th style="padding:.6rem 1.1rem;text-align:right;font-weight:600;font-size:.8rem;color:var(--text-muted,#6b7280)">متن سوال</th>
                                    <th style="padding:.6rem 1.1rem;text-align:right;font-weight:600;font-size:.8rem;color:var(--text-muted,#6b7280)">سطح سختی</th>
                                    <th style="padding:.6rem 1.1rem;text-align:right;font-weight:600;font-size:.8rem;color:var(--text-muted,#6b7280)">وضعیت</th>
                                    <th style="padding:.6rem 1.1rem;text-align:right;font-weight:600;font-size:.8rem;color:var(--text-muted,#6b7280)"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subGroup['questions'] as $q)
                                    <tr style="border-top:1px solid var(--border,#e5e7eb)">
                                        <td style="padding:.75rem 1.1rem">{{ \Illuminate\Support\Str::limit($q->question_text ?? '(تصویر)', 60) }}</td>
                                        <td style="padding:.75rem 1.1rem;white-space:nowrap">{{ $q->difficulty }}</td>
                                        <td style="padding:.75rem 1.1rem;white-space:nowrap">
                                            @php
                                                $statusLabel = match($q->status) {
                                                    'draft' => 'پیش‌نویس',
                                                    'pending' => 'در انتظار بررسی',
                                                    'approved' => 'تأیید شده',
                                                    'rejected' => 'رد شده',
                                                    default => $q->status,
                                                };
                                            @endphp
                                            {{ $statusLabel }}
                                        </td>
                                        <td style="padding:.75rem 1.1rem;white-space:nowrap">
                                            <div style="display:flex;gap:.6rem;align-items:center;justify-content:flex-start">

                                                <a href="{{ \App\Filament\Resources\QuestionResource::getUrl('edit', ['record' => $q]) }}" style="color:rgb(99,102,241);font-weight:600;text-decoration:none">
                                                    ویرایش
                                                </a>

                                                @if ($isReviewer && $q->status === 'pending')
                                                    <button
                                                        wire:click="reviewSingleQuestion({{ $q->id }}, 'approve')"
                                                        style="background:rgb(22,163,74);color:#fff;font-weight:600;font-size:.75rem;padding:.3rem .7rem;border-radius:.5rem;border:none;cursor:pointer">
                                                        تأیید
                                                    </button>
                                                    <button
                                                        wire:click="reviewSingleQuestion({{ $q->id }}, 'reject')"
                                                        style="background:rgb(220,38,38);color:#fff;font-weight:600;font-size:.75rem;padding:.3rem .7rem;border-radius:.5rem;border:none;cursor:pointer">
                                                        رد
                                                    </button>
                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </details>

                @endforeach
            </div>

        @endif

    @endif

</x-filament-panels::page>
