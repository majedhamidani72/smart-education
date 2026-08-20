<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Forms\Components\JalaliDateTimePicker;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * خریدها
 * --------------------------------------------------------------------
 * این Resource فقط-نمایشی است (Read-only) — خریدها توسط خودِ
 * اپلیکیشن و درگاه پرداخت ثبت می‌شوند، نه دستی توسط ادمین. تنها
 * کاری که ادمین می‌تواند از این پنل انجام دهد، تغییر دستی وضعیت
 * (مثلاً ثبت یک بازگشت وجه) و افزودن یادداشت پشتیبانی است — برای
 * همین یک فرم ویرایش محدود (فقط status و notes) وجود دارد، اما
 * دکمه‌ی «ایجاد» اصلاً در دسترس نیست.
 */
class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'مدیریت مالی';

    protected static ?string $navigationLabel = 'خریدها';

    protected static ?string $modelLabel = 'خرید';

    protected static ?string $pluralModelLabel = 'خریدها';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Placeholder::make('invoice_number')
                ->label('شماره فاکتور')
                ->content(fn(?Purchase $record) => $record?->invoice_number ?? '—'),

            Forms\Components\Placeholder::make('user')
                ->label('خریدار')
                ->content(fn(?Purchase $record) => $record?->user?->name ?? '—'),

            Forms\Components\Placeholder::make('payable_amount')
                ->label('مبلغ قابل پرداخت')
                ->content(fn(?Purchase $record) => $record
                    ? number_format($record->payable_amount).' تومان'
                    : '—'),

            // فقط این دو فیلد قابل ویرایش دستی هستند — برای
            // پشتیبانی (مثلاً ثبت بازگشت وجه یا لغو دستی).
            Forms\Components\Select::make('status')
                ->label('وضعیت')
                ->options([
                    'pending' => 'در انتظار پرداخت',
                    'paid' => 'پرداخت‌شده',
                    'cancelled' => 'لغوشده',
                    'refunded' => 'بازگشت وجه',
                ])
                ->required(),

            Forms\Components\Textarea::make('notes')
                ->label('یادداشت پشتیبانی')
                ->rows(3)
                ->columnSpanFull(),

        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            InfolistSection::make('اطلاعات خرید')

                ->columns(3)

                ->schema([

                    TextEntry::make('invoice_number')
                        ->label('شماره فاکتور'),

                    TextEntry::make('user.name')
                        ->label('خریدار'),

                    TextEntry::make('user.mobile')
                        ->label('موبایل خریدار'),

                    TextEntry::make('total_amount')
                        ->label('مبلغ کل')
                        ->formatStateUsing(fn($state) => number_format($state).' تومان'),

                    TextEntry::make('discount_amount')
                        ->label('مبلغ تخفیف')
                        ->formatStateUsing(fn($state) => number_format($state).' تومان'),

                    TextEntry::make('payable_amount')
                        ->label('مبلغ قابل پرداخت')
                        ->formatStateUsing(fn($state) => number_format($state).' تومان')
                        ->weight('bold'),

                    TextEntry::make('status')
                        ->label('وضعیت')
                        ->badge()
                        ->formatStateUsing(fn($state) => match ($state) {
                            'pending' => 'در انتظار پرداخت',
                            'paid' => 'پرداخت‌شده',
                            'cancelled' => 'لغوشده',
                            'refunded' => 'بازگشت وجه',
                            default => $state,
                        })
                        ->color(fn($state) => match ($state) {
                            'paid' => 'success',
                            'pending' => 'warning',
                            'cancelled' => 'gray',
                            'refunded' => 'danger',
                            default => 'gray',
                        }),

                    TextEntry::make('paid_at')
                        ->label('تاریخ پرداخت')
                        ->formatStateUsing(fn($state) => $state ? \App\Support\Jalali::format($state) : '—'),

                    TextEntry::make('created_at')
                        ->label('تاریخ ثبت')
                        ->formatStateUsing(fn($state) => \App\Support\Jalali::format($state)),

                    TextEntry::make('notes')
                        ->label('یادداشت')
                        ->placeholder('—')
                        ->columnSpanFull(),

                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('شماره فاکتور')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('خریدار')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.mobile')
                    ->label('موبایل')
                    ->searchable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('تعداد آیتم')
                    ->counts('items'),

                Tables\Columns\TextColumn::make('payable_amount')
                    ->label('مبلغ قابل پرداخت')
                    ->formatStateUsing(fn($state) => number_format($state).' تومان')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('وضعیت')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'gray' => 'cancelled',
                        'danger' => 'refunded',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'در انتظار پرداخت',
                        'paid' => 'پرداخت‌شده',
                        'cancelled' => 'لغوشده',
                        'refunded' => 'بازگشت وجه',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاریخ پرداخت')
                    ->formatStateUsing(fn($state) => $state ? \App\Support\Jalali::format($state) : '—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn($state) => \App\Support\Jalali::format($state))
                    ->sortable(),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'pending' => 'در انتظار پرداخت',
                        'paid' => 'پرداخت‌شده',
                        'cancelled' => 'لغوشده',
                        'refunded' => 'بازگشت وجه',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->label('بازه‌ی تاریخ ثبت')
                    ->form([
                        JalaliDateTimePicker::make('from')->label('از'),
                        JalaliDateTimePicker::make('until')->label('تا'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) $indicators[] = 'از: '.$data['from'];
                        if ($data['until'] ?? null) $indicators[] = 'تا: '.$data['until'];
                        return $indicators;
                    }),

                Tables\Filters\TrashedFilter::make(),

            ])

            ->actions([

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->label('تغییر وضعیت'),

            ])

            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'view' => Pages\ViewPurchase::route('/{record}'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['user']);
    }
}
