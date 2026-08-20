<x-filament-panels::page>

    @php
        $groups = $this->getGroupedSettings();
    @endphp

    <div style="overflow-x:auto;border:1px solid var(--border,#e5e7eb);border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,0.06)">
        <table style="width:100%;border-collapse:collapse;font-size:0.95rem">
            <thead>
                <tr style="background:var(--surface-2,#f9fafb)">
                    <th style="padding:0.85rem 1rem;text-align:right;width:2.5rem"></th>
                    <th style="padding:0.85rem 1rem;text-align:right;font-weight:600">عنوان تنظیم</th>
                    <th style="padding:0.85rem 1rem;text-align:right;font-weight:600">مقدار</th>
                    <th style="padding:0.85rem 1rem;text-align:right;font-weight:600">آخرین به‌روزرسانی</th>
                    <th style="padding:0.85rem 1rem;text-align:right;font-weight:600"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groups as $group)

                    @foreach ($group['items'] as $index => $setting)
                        <tr style="background:{{ $group['bg'] }};border-top:1px solid rgba(0,0,0,0.05)">

                            @if ($index === 0)
                                <td rowspan="{{ $group['items']->count() }}"
                                    style="padding:0;border-left:3px solid {{ $group['accent'] }};vertical-align:middle;text-align:center">
                                    <div style="writing-mode:vertical-rl;transform:rotate(180deg);font-weight:700;color:{{ $group['accent'] }};padding:0.75rem 0.4rem;white-space:nowrap;letter-spacing:0.05em">
                                        {{ $group['name'] }}
                                    </div>
                                </td>
                            @endif

                            <td style="padding:0.85rem 1rem;font-weight:600">{{ $setting->description }}</td>

                            <td style="padding:0.85rem 1rem;color:var(--text-secondary,#4b5563);max-width:28rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                {{ \Illuminate\Support\Str::limit($setting->value, 90) }}
                            </td>

                            <td style="padding:0.85rem 1rem;color:var(--text-muted,#6b7280);font-size:0.85rem;white-space:nowrap">
                                {{ $setting->updated_at?->format('Y-m-d H:i') }}
                            </td>

                            <td style="padding:0.85rem 1rem;white-space:nowrap">
                                <a href="{{ \App\Filament\Resources\SettingResource::getUrl('edit', ['record' => $setting]) }}"
                                   style="color:{{ $group['accent'] }};font-weight:600;text-decoration:none;font-size:0.875rem">
                                    ویرایش
                                </a>
                            </td>

                        </tr>
                    @endforeach

                @endforeach
            </tbody>
        </table>
    </div>

</x-filament-panels::page>
