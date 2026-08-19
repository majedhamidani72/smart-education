<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentTransactionResource\Pages;
use App\Models\PaymentTransaction;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * تراکنش‌های پرداخت
 * --------------------------------------------------------------------
 * کاملاً فقط-نمایشی است — این جدول لاگ خام رفت‌وآمد با درگاه
 * بانکی است (چه موفق چه ناموفق) و هیچ‌وقت نباید دستی از پنل
 * تغییر کند؛ برای بررسی و پیگیری پرداخت‌هاست، نه ویرایش آن‌ها.
 */
class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'مدیریت مالی';

    protected static ?string $navigationLabel = 'تراکنش‌های پرداخت';

    protected static ?string $modelLabel = 'تراکنش';

    protected static ?string $pluralModelLabel = 'تراکنش‌های پرداخت';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            InfolistSection::make('اطلاعات تراکنش')

                ->columns(3)

                ->schema([

                    TextEntry::make('purchase.invoice_number')
                        ->label('شماره فاکتور مرتبط'),

                    TextEntry::make('user.name')
                        ->label('کاربر'),

                    TextEntry::make('gateway')
                        ->label('درگاه پرداخت'),

                    TextEntry::make('amount')
                        ->label('مبلغ')
                        ->formatStateUsing(fn($state) => number_format($state).' تومان'),

                    TextEntry::make('status')
                        ->label('وضعیت')
                        ->badge()
                        ->formatStateUsing(fn($state) => match ($state) {
                            'pending' => 'در انتظار',
                            'paid' => 'موفق',
                            'failed' => 'ناموفق',
                            'cancelled' => 'لغوشده',
                            default => $state,
                        })
                        ->color(fn($state) => match ($state) {
                            'paid' => 'success',
                            'pending' => 'warning',
                            'failed', 'cancelled' => 'danger',
                            default => 'gray',
                        }),

                    TextEntry::make('card_pan')
                        ->label('شماره کارت')
                        ->placeholder('—'),

                    TextEntry::make('transaction_id')
                        ->label('شناسه‌ی تراکنش درگاه')
                        ->placeholder('—')
                        ->copyable(),

                    TextEntry::make('reference_id')
                        ->label('کد پیگیری')
                        ->placeholder('—')
                        ->copyable(),

                    TextEntry::make('paid_at')
                        ->label('تاریخ پرداخت')
                        ->formatStateUsing(fn($state) => $state ? \App\Support\Jalali::format($state) : '—'),

                    TextEntry::make('verified_at')
                        ->label('تاریخ تایید')
                        ->formatStateUsing(fn($state) => $state ? \App\Support\Jalali::format($state) : '—'),

                    TextEntry::make('message')
                        ->label('پیام درگاه')
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

                Tables\Columns\TextColumn::make('purchase.invoice_number')
                    ->label('شماره فاکتور')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('کاربر')
                    ->searchable(),

                Tables\Columns\TextColumn::make('gateway')
                    ->label('درگاه'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('مبلغ')
                    ->formatStateUsing(fn($state) => number_format($state).' تومان')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('وضعیت')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'در انتظار',
                        'paid' => 'موفق',
                        'failed' => 'ناموفق',
                        'cancelled' => 'لغوشده',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('card_pan')
                    ->label('شماره کارت')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn($state) => \App\Support\Jalali::format($state))
                    ->sortable(),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'pending' => 'در انتظار',
                        'paid' => 'موفق',
                        'failed' => 'ناموفق',
                        'cancelled' => 'لغوشده',
                    ]),

                Tables\Filters\SelectFilter::make('gateway')
                    ->label('درگاه')
                    ->options(fn() => PaymentTransaction::query()
                        ->distinct()
                        ->pluck('gateway', 'gateway')
                        ->toArray()),

            ])

            ->actions([

                Tables\Actions\ViewAction::make(),

            ])

            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentTransactions::route('/'),
            'view' => Pages\ViewPaymentTransaction::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['purchase', 'user']);
    }
}
