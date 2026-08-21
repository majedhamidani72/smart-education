<x-filament-panels::page>

    @php
        $palette = [
            ['bg' => 'rgba(99,102,241,0.06)', 'accent' => 'rgb(99,102,241)'],
            ['bg' => 'rgba(20,184,166,0.06)', 'accent' => 'rgb(20,184,166)'],
            ['bg' => 'rgba(234,179,8,0.06)', 'accent' => 'rgb(202,138,4)'],
            ['bg' => 'rgba(217,70,239,0.06)', 'accent' => 'rgb(217,70,239)'],
            ['bg' => 'rgba(239,68,68,0.06)', 'accent' => 'rgb(220,38,38)'],
        ];
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
                        style="text-align:right;cursor:pointer;background:{{ $colors['bg'] }};border:1px solid {{ $colors['accent'] }}33;border-right:4px solid {{ $colors['accent'] }};border-radius:1rem;padding:1.1rem 1.3rem;transition:transform .15s"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'">

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

    {{-- سطح ۳: لیست واقعی سوالات --}}
    @else

        @php $questions = $this->getFilteredQuestions(); @endphp

        <button wire:click="backToExamLevels" style="margin-bottom:1.25rem;font-size:.85rem;color:var(--text-muted,#6b7280);background:none;border:none;cursor:pointer">
            → بازگشت
        </button>

        @if ($questions->isEmpty())
            <div style="text-align:center;padding:3rem;color:var(--text-muted,#6b7280)">
                سوالی در این دسته یافت نشد.
            </div>
        @else
            <div style="overflow-x:auto;border:1px solid var(--border,#e5e7eb);border-radius:1rem">
                <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                    <thead>
                        <tr style="background:var(--surface-2,#f9fafb)">
                            <th style="padding:.75rem 1rem;text-align:right">متن سوال</th>
                            <th style="padding:.75rem 1rem;text-align:right">سطح سختی</th>
                            <th style="padding:.75rem 1rem;text-align:right">وضعیت</th>
                            <th style="padding:.75rem 1rem;text-align:right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($questions as $q)
                            <tr style="border-top:1px solid var(--border,#e5e7eb)">
                                <td style="padding:.75rem 1rem">{{ \Illuminate\Support\Str::limit($q->question_text ?? '(تصویر)', 60) }}</td>
                                <td style="padding:.75rem 1rem">{{ $q->difficulty }}</td>
                                <td style="padding:.75rem 1rem">{{ $q->status }}</td>
                                <td style="padding:.75rem 1rem">
                                    <a href="{{ \App\Filament\Resources\QuestionResource::getUrl('edit', ['record' => $q]) }}" style="color:rgb(99,102,241);font-weight:600;text-decoration:none">
                                        ویرایش
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    @endif

</x-filament-panels::page>
