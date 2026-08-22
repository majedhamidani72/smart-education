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
        $breadcrumb = $this->getBreadcrumbPath();
    @endphp

    @if (! empty($breadcrumb))
        <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;margin-bottom:1.25rem;font-size:.85rem;color:var(--text-muted,#6b7280);background:var(--surface-2,#f9fafb);border:1px solid var(--border,#e5e7eb);border-radius:.75rem;padding:.6rem 1rem">
            <span style="font-weight:600;color:var(--text-secondary,#4b5563)">📍 مسیر:</span>
            @foreach ($breadcrumb as $index => $crumb)
                @if ($crumb['action'])
                    <button wire:click="{{ $crumb['action'] }}" style="background:none;border:none;color:rgb(99,102,241);font-weight:600;cursor:pointer;padding:0;font-size:.85rem">{{ $crumb['label'] }}</button>
                @else
                    <span style="font-weight:600">{{ $crumb['label'] }}</span>
                @endif
                @if (! $loop->last)
                    <span style="color:var(--text-muted,#9ca3af)">←</span>
                @endif
            @endforeach
        </div>
    @endif

    {{-- سطح ۱: اپلیکیشن / ایجادکننده / پایه / کتاب --}}
    @if ($viewLevel === 'groups')

        @php $groups = $this->getGroups(); @endphp

        @if ($groups->isEmpty())
            <div style="text-align:center;padding:3rem;color:var(--text-muted,#6b7280)">
                هنوز هیچ محتوایی ثبت نشده است. برای شروع، دکمه‌ی «ایجاد محتوای آموزشی» بالا سمت راست را بزن.
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

                        @if (! $isReviewer && $group['draft_count'] > 0)
                            <span style="position:absolute;top:-.5rem;left:-.5rem;background:rgb(100,116,139);color:#fff;font-size:.7rem;font-weight:700;border-radius:999px;padding:.15rem .5rem">
                                {{ $group['draft_count'] }} پیش‌نویس
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
                            {{ $group['count'] }} محتوا ←
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

    {{-- سطح ۲: نوع محتوا (تدریس / گام‌به‌گام / نمونه سوالات) --}}
    @elseif ($viewLevel === 'contentTypes')

        @php $typeCounts = $this->getContentTypeCounts(); @endphp

        <button wire:click="backToGroups" style="margin-bottom:1.25rem;display:inline-flex;align-items:center;gap:.4rem;background:var(--surface-2,#f1f5f9);color:var(--text-secondary,#4b5563);font-weight:600;font-size:.85rem;padding:.5rem 1rem;border-radius:.6rem;border:none;cursor:pointer">
            ← بازگشت به لیست کتاب‌ها
        </button>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem">
            @foreach ($typeCounts as $i => $type)
                @php $colors = $palette[$i % count($palette)]; @endphp
                <button wire:click="selectContentType('{{ $type['slug'] }}')"
                    style="position:relative;text-align:right;cursor:pointer;background:{{ $colors['bg'] }};border:1px solid {{ $colors['accent'] }}33;border-right:4px solid {{ $colors['accent'] }};border-radius:1rem;padding:1.25rem">

                    @if ($isReviewer && $type['pending_count'] > 0)
                        <span style="position:absolute;top:-.5rem;left:-.5rem;background:rgb(220,38,38);color:#fff;font-size:.7rem;font-weight:700;border-radius:999px;padding:.15rem .5rem">
                            {{ $type['pending_count'] }} در انتظار
                        </span>
                    @endif

                    @if (! $isReviewer && $type['draft_count'] > 0)
                        <span style="position:absolute;top:-.5rem;left:-.5rem;background:rgb(100,116,139);color:#fff;font-size:.7rem;font-weight:700;border-radius:999px;padding:.15rem .5rem">
                            {{ $type['draft_count'] }} پیش‌نویس
                        </span>
                    @endif

                    <div style="font-weight:700;color:{{ $colors['accent'] }}">
                        @if ($type['slug'] === 'teaching') 🎥
                        @elseif ($type['slug'] === 'step_by_step') 📝
                        @else 📄
                        @endif
                        {{ $type['title'] }}
                    </div>
                    <div style="font-size:1.5rem;font-weight:800;margin-top:.5rem">{{ $type['count'] }}</div>
                    <div style="font-size:.75rem;color:var(--text-muted,#6b7280)">محتوا</div>
                </button>
            @endforeach
        </div>

    {{-- سطح ۳: محتواها به تفکیک فصل/بخش، قابل‌جمع‌شدن --}}
    @else

        @php
            $grouped = $this->getGroupedItems();
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

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">

            <button wire:click="backToContentTypes" style="display:inline-flex;align-items:center;gap:.4rem;background:var(--surface-2,#f1f5f9);color:var(--text-secondary,#4b5563);font-weight:600;font-size:.85rem;padding:.5rem 1rem;border-radius:.6rem;border:none;cursor:pointer">
                ← بازگشت به نوع محتوا
            </button>

        </div>

        @if ($grouped->isEmpty())

            @php
                $typeLabel = match ($selectedContentTypeSlug) {
                    'teaching' => 'تدریس',
                    'step_by_step' => 'گام‌به‌گام',
                    'sample_questions' => 'نمونه سوال',
                    default => 'محتوا',
                };

                $typeLabelPossessive = match ($selectedContentTypeSlug) {
                    'teaching' => 'تدریسی',
                    'step_by_step' => 'گام‌به‌گامی',
                    'sample_questions' => 'نمونه سوالی',
                    default => 'محتوایی',
                };
            @endphp

            <div style="text-align:center;padding:3rem;color:var(--text-muted,#6b7280);display:flex;flex-direction:column;align-items:center;gap:1rem">
                <div>هنوز هیچ {{ $typeLabelPossessive }} برای این کتاب ثبت نشده است.</div>
                <a href="{{ \App\Filament\Resources\ContentItemResource::getUrl('create', [
                        'book_id' => $selectedBookId,
                        'content_type_id' => $this->getSelectedContentTypeId(),
                    ]) }}"
                   style="background:rgb(20,184,166);color:#fff;font-weight:600;font-size:.85rem;padding:.6rem 1.2rem;border-radius:.7rem;text-decoration:none">
                    + ایجاد {{ $typeLabel }} برای این کتاب
                </a>
            </div>
        @else

            <div style="display:flex;flex-direction:column;gap:.85rem">
                @foreach ($grouped as $subGroup)

                    @php
                        $chapterColors = $chapterPalette[crc32((string) $subGroup['chapter_id']) % count($chapterPalette)];
                    @endphp

                    <div style="border:1px solid {{ $chapterColors['accent'] }}33;border-right:4px solid {{ $chapterColors['accent'] }};border-radius:1rem;overflow:hidden">

                        <div wire:click="toggleGroup('{{ $subGroup['key'] }}')" style="cursor:pointer;background:{{ $chapterColors['bg'] }};padding:.85rem 1.1rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem">

                            <div style="font-weight:700;font-size:.9rem;color:{{ $chapterColors['accent'] }}">
                                <span style="display:inline-block;transition:transform .15s;transform:rotate({{ in_array($subGroup['key'], $expandedGroups) ? '90deg' : '0deg' }})">◀</span>
                                @if ($subGroup['section_title'])
                                    فصل: {{ $subGroup['chapter_title'] }} — بخش/درس: {{ $subGroup['section_title'] }}
                                @else
                                    فصل: {{ $subGroup['chapter_title'] }}
                                @endif
                                <span style="font-weight:400;color:var(--text-muted,#6b7280);font-size:.8rem">({{ $subGroup['items']->count() }} محتوا)</span>

                                @if ($isReviewer && $subGroup['pending_count'] > 0)
                                    <span style="background:rgb(202,138,4);color:#fff;font-size:.7rem;font-weight:700;border-radius:999px;padding:.15rem .55rem;margin-right:.4rem">
                                        {{ $subGroup['pending_count'] }} در انتظار بررسی
                                    </span>
                                @endif

                                @if (! $isReviewer && $subGroup['draft_count'] > 0)
                                    <span style="background:rgb(100,116,139);color:#fff;font-size:.7rem;font-weight:700;border-radius:999px;padding:.15rem .55rem;margin-right:.4rem">
                                        {{ $subGroup['draft_count'] }} پیش‌نویس (ارسال‌نشده)
                                    </span>
                                @endif
                            </div>

                            <div style="display:flex;gap:.5rem;flex-wrap:wrap" onclick="event.stopPropagation()">

                                @if ($isReviewer && $subGroup['pending_count'] > 0)
                                    <button
                                        wire:click="approveGroup({{ $subGroup['chapter_id'] }}, {{ $subGroup['section_id'] ?? 'null' }})"
                                        wire:confirm="همه‌ی {{ $subGroup['pending_count'] }} محتوای در انتظار این قسمت تأیید شوند؟"
                                        style="background:rgb(22,163,74);color:#fff;font-weight:600;font-size:.8rem;padding:.4rem .9rem;border-radius:.6rem;border:none;cursor:pointer">
                                        ✅ تأیید همه
                                    </button>
                                    <button
                                        wire:click="startRejectGroup('{{ $subGroup['key'] }}')"
                                        style="background:rgb(220,38,38);color:#fff;font-weight:600;font-size:.8rem;padding:.4rem .9rem;border-radius:.6rem;border:none;cursor:pointer">
                                        ❌ رد همه
                                    </button>
                                @endif

                                @if (! $isReviewer && $subGroup['draft_count'] > 0)
                                    <button
                                        wire:click="submitGroupForReview({{ $subGroup['chapter_id'] }}, {{ $subGroup['section_id'] ?? 'null' }})"
                                        wire:confirm="همه‌ی {{ $subGroup['draft_count'] }} محتوای پیش‌نویس این قسمت برای بررسی ارسال شوند؟"
                                        style="background:rgb(79,70,229);color:#fff;font-weight:600;font-size:.8rem;padding:.4rem .9rem;border-radius:.6rem;border:none;cursor:pointer">
                                        📤 ارسال همه برای بررسی
                                    </button>
                                @endif

                                @php
                                    $typeLabel = match ($selectedContentTypeSlug) {
                                        'teaching' => 'تدریس',
                                        'step_by_step' => 'گام‌به‌گام',
                                        'sample_questions' => 'نمونه سوال',
                                        default => 'محتوا',
                                    };
                                @endphp

                                <a href="{{ \App\Filament\Resources\ContentItemResource::getUrl('create', [
                                        'book_id' => $selectedBookId,
                                        'chapter_id' => $subGroup['chapter_id'],
                                        'section_id' => $subGroup['section_id'],
                                        'content_type_id' => $this->getSelectedContentTypeId(),
                                    ]) }}"
                                   style="background:rgb(20,184,166);color:#fff;font-weight:600;font-size:.8rem;padding:.4rem .9rem;border-radius:.6rem;text-decoration:none;white-space:nowrap">
                                    ادامه‌ی ایجاد {{ $typeLabel }} برای همین قسمت ←
                                </a>

                            </div>

                        </div>

                        {{-- فرم اینلاین دلیل رد گروهی --}}
                        @if ($rejectingGroupKey === $subGroup['key'])
                            <div style="padding:.85rem 1.1rem;background:rgba(220,38,38,0.05);border-top:1px solid rgba(220,38,38,0.2);display:flex;gap:.6rem;align-items:flex-start;flex-wrap:wrap">
                                <input type="text" wire:model="rejectionReasonInput" placeholder="دلیل رد را بنویس..." style="flex:1;min-width:200px;padding:.5rem .75rem;border-radius:.5rem;border:1px solid rgba(220,38,38,0.3);font-size:.85rem">
                                <button wire:click="confirmRejectGroup({{ $subGroup['chapter_id'] }}, {{ $subGroup['section_id'] ?? 'null' }})" style="background:rgb(220,38,38);color:#fff;font-weight:600;font-size:.8rem;padding:.5rem .9rem;border-radius:.5rem;border:none;cursor:pointer">ثبت رد</button>
                                <button wire:click="cancelReject" style="background:var(--surface-2,#f1f5f9);font-weight:600;font-size:.8rem;padding:.5rem .9rem;border-radius:.5rem;border:none;cursor:pointer">انصراف</button>
                            </div>
                        @endif

                        @if (in_array($subGroup['key'], $expandedGroups))

                            @foreach ($subGroup['by_type'] as $typeGroup)

                                <div style="border-top:1px solid var(--border,#e5e7eb)">

                                    <div style="padding:.55rem 1.1rem;background:{{ $typeGroup['accent'] }}0d;font-weight:700;font-size:.82rem;color:{{ $typeGroup['accent'] }}">
                                        {{ $typeGroup['icon'] }} {{ $typeGroup['label'] }}
                                        <span style="font-weight:400;color:var(--text-muted,#6b7280);font-size:.75rem">({{ $typeGroup['items']->count() }})</span>
                                    </div>

                                    <table style="width:100%;border-collapse:collapse;font-size:.9rem">
                                        <thead>
                                            <tr style="background:var(--surface-2,#f9fafb)">
                                                <th style="padding:.6rem 1.1rem;text-align:right;font-weight:600;font-size:.8rem;color:var(--text-muted,#6b7280)">عنوان</th>
                                                <th style="padding:.6rem 1.1rem;text-align:right;font-weight:600;font-size:.8rem;color:var(--text-muted,#6b7280)">رایگان</th>
                                                <th style="padding:.6rem 1.1rem;text-align:right;font-weight:600;font-size:.8rem;color:var(--text-muted,#6b7280)">وضعیت</th>
                                                <th style="padding:.6rem 1.1rem;text-align:right;font-weight:600;font-size:.8rem;color:var(--text-muted,#6b7280)"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($typeGroup['items'] as $item)
                                                <tr style="border-top:1px solid var(--border,#e5e7eb)">
                                                    <td style="padding:.75rem 1.1rem">{{ \Illuminate\Support\Str::limit($item->title, 50) }}</td>
                                                    <td style="padding:.75rem 1.1rem;white-space:nowrap">{{ $item->is_free ? '✅' : '—' }}</td>
                                                    <td style="padding:.75rem 1.1rem;white-space:nowrap">
                                                        @php
                                                            $statusLabel = match($item->status) {
                                                                'draft' => 'پیش‌نویس',
                                                                'pending' => 'در انتظار بررسی',
                                                                'approved' => 'تأیید شده',
                                                                'rejected' => 'رد شده',
                                                                'published' => 'منتشر شده',
                                                                default => $item->status,
                                                            };
                                                        @endphp
                                                        {{ $statusLabel }}
                                                        @if ($item->status === 'rejected' && $item->rejection_reason)
                                                            <div style="font-size:.72rem;color:rgb(220,38,38);margin-top:.2rem">{{ $item->rejection_reason }}</div>
                                                        @endif
                                                    </td>
                                                    <td style="padding:.75rem 1.1rem;white-space:nowrap">
                                                        <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">

                                                            <a href="{{ \App\Filament\Resources\ContentItemResource::getUrl('edit', [
                                                                    'record' => $item,
                                                                    'book_id' => $selectedBookId,
                                                                    'chapter_id' => $subGroup['chapter_id'],
                                                                    'section_id' => $subGroup['section_id'],
                                                                    'content_type_id' => $this->getSelectedContentTypeId(),
                                                                ]) }}" style="color:rgb(99,102,241);font-weight:600;text-decoration:none">
                                                                {{ (! $isReviewer && $item->status === 'pending') ? 'نمایش' : 'ویرایش' }}
                                                            </a>

                                                            @if ($isReviewer && $item->status === 'pending')
                                                                <button
                                                                    wire:click="reviewSingleItem({{ $item->id }}, 'approve')"
                                                                    style="background:rgb(22,163,74);color:#fff;font-weight:600;font-size:.75rem;padding:.3rem .7rem;border-radius:.5rem;border:none;cursor:pointer">
                                                                    تأیید
                                                                </button>
                                                                <button
                                                                    wire:click="startRejectItem({{ $item->id }})"
                                                                    style="background:rgb(220,38,38);color:#fff;font-weight:600;font-size:.75rem;padding:.3rem .7rem;border-radius:.5rem;border:none;cursor:pointer">
                                                                    رد
                                                                </button>
                                                            @endif

                                                            @if (! $isReviewer && $item->status === 'draft')
                                                                <button
                                                                    wire:click="submitSingleForReview({{ $item->id }})"
                                                                    style="background:rgb(79,70,229);color:#fff;font-weight:600;font-size:.75rem;padding:.3rem .7rem;border-radius:.5rem;border:none;cursor:pointer">
                                                                    📤 ارسال برای بررسی
                                                                </button>
                                                            @endif

                                                        </div>

                                                        {{-- فرم اینلاین دلیل رد تکی --}}
                                                        @if ($rejectingItemId === $item->id)
                                                            <div style="margin-top:.5rem;display:flex;gap:.4rem;align-items:center;flex-wrap:wrap">
                                                                <input type="text" wire:model="rejectionReasonInput" placeholder="دلیل رد..." style="flex:1;min-width:150px;padding:.35rem .6rem;border-radius:.4rem;border:1px solid rgba(220,38,38,0.3);font-size:.78rem">
                                                                <button wire:click="confirmRejectItem" style="background:rgb(220,38,38);color:#fff;font-weight:600;font-size:.72rem;padding:.35rem .7rem;border-radius:.4rem;border:none;cursor:pointer">ثبت</button>
                                                                <button wire:click="cancelReject" style="background:var(--surface-2,#f1f5f9);font-weight:600;font-size:.72rem;padding:.35rem .7rem;border-radius:.4rem;border:none;cursor:pointer">لغو</button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                </div>

                            @endforeach

                        @endif

                    </div>

                @endforeach
            </div>

        @endif

    @endif

</x-filament-panels::page>
