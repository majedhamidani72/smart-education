<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherEarningResource\Pages;
use App\Filament\Forms\Components\JalaliDateTimePicker;
use App\Models\TeacherEarning;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * درآمد معلمان
 * --------------------------------------------------------------------
 * فقط-نمایشی است — رکوردها خودکار بعد از هر پرداخت موفق ساخته
 * می‌شوند (در PaymentService). تنها کاری که ادمین از این پنل
 * انجام می‌دهد، تسویه‌ی دستی (ثبت شماره‌ی تسویه و تغییر وضعیت
 * به «پرداخت‌شده») است.
 */
class TeacherEarningResource extends Resource
{
    protected static ?string $model = TeacherEarning::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'مدیریت مالی';

    protected static ?string $navigationLabel = 'درآمد معلمان';

    protected static ?string $modelLabel = 'درآمد معلم';

    protected static ?string $pluralModelLabel = 'درآمد معلمان';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Select::make('status')
                ->label('وضعیت')
                ->options([
                    'pending' => 'در انتظار تسویه',
                    'paid' => 'تسویه‌شده',
                    'cancelled' => 'لغوشده',
                ])
                ->required()
                ->live(),

            Forms\Components\TextInput::make('settlement_number')
                ->label('شماره تسویه')
                ->visible(fn(Forms\Get $get) => $get('status') === 'paid'),

            JalaliDateTimePicker::make('paid_at')
                ->label('تاریخ تسویه')
                ->visible(fn(Forms\Get $get) => $get('status') === 'paid')
                ->default(now()->format('Y-m-d H:i:s')),

            Forms\Components\Textarea::make('notes')
                ->label('یادداشت')
                ->rows(2)
                ->columnSpanFull(),

        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            InfolistSection::make('جزئیات')

                ->columns(3)

                ->schema([

                    TextEntry::make('teacher.name')
                        ->label('معلم'),

                    TextEntry::make('purchase.invoice_number')
                        ->label('شماره فاکتور'),

                    TextEntry::make('purchaseItem.title')
                        ->label('آیتم فروخته‌شده'),

                    TextEntry::make('sale_amount')
                        ->label('مبلغ فروش')
                        ->formatStateUsing(fn($state) => number_format($state).' تومان'),

                    TextEntry::make('percentage')
                        ->label('درصد سهم')
                        ->formatStateUsing(fn($state) => $state.'٪'),

                    TextEntry::make('amount')
                        ->label('سهم معلم')
                        ->formatStateUsing(fn($state) => number_format($state).' تومان')
                        ->weight('bold'),

                    TextEntry::make('status')
                        ->label('وضعیت')
                        ->badge()
                        ->formatStateUsing(fn($state) => match ($state) {
                            'pending' => 'در انتظار تسویه',
                            'paid' => 'تسویه‌شده',
                            'cancelled' => 'لغوشده',
                            default => $state,
                        }),

                    TextEntry::make('settlement_number')
                        ->label('شماره تسویه')
                        ->placeholder('—'),

                    TextEntry::make('paid_at')
                        ->label('تاریخ تسویه')
                        ->formatStateUsing(fn($state) => $state ? \App\Support\Jalali::format($state) : '—'),

                    TextEntry::make('created_at')
                        ->label('تاریخ ثبت')
                        ->formatStateUsing(fn($state) => \App\Support\Jalali::format($state)),

                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('معلم')
                    ->searchable(),

                Tables\Columns\TextColumn::make('teacher.teacherProfile.card_number')
                    ->label('شماره کارت')
                    ->placeholder('ثبت نشده')
                    ->copyable()
                    ->copyMessage('شماره کارت کپی شد.'),

                Tables\Columns\TextColumn::make('purchase.invoice_number')
                    ->label('شماره فاکتور')
                    ->searchable(),

                Tables\Columns\TextColumn::make('purchaseItem.title')
                    ->label('آیتم')
                    ->limit(30),

                Tables\Columns\TextColumn::make('sale_amount')
                    ->label('مبلغ فروش')
                    ->formatStateUsing(fn($state) => number_format($state).' تومان'),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('درصد')
                    ->formatStateUsing(fn($state) => $state.'٪'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('سهم معلم')
                    ->formatStateUsing(fn($state) => number_format($state).' تومان')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('وضعیت')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'در انتظار تسویه',
                        'paid' => 'تسویه‌شده',
                        'cancelled' => 'لغوشده',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn($state) => \App\Support\Jalali::format($state))
                    ->sortable(),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('معلم')
                    ->relationship('teacher', 'name')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'pending' => 'در انتظار تسویه',
                        'paid' => 'تسویه‌شده',
                        'cancelled' => 'لغوشده',
                    ]),

            ])

            ->actions([

                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('settle')
                    ->label('تسویه شد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(TeacherEarning $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([

                        Forms\Components\TextInput::make('settlement_number')
                            ->label('شماره تسویه')
                            ->required(),

                    ])
                    ->action(function (TeacherEarning $record, array $data) {

                        $record->update([
                            'status' => 'paid',
                            'settlement_number' => $data['settlement_number'],
                            'paid_at' => now(),
                        ]);

                        Notification::make()
                            ->title('تسویه با موفقیت ثبت شد.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->label('ویرایش'),

            ])

            ->bulkActions([

                Tables\Actions\BulkAction::make('settleBulk')
                    ->label('تسویه‌ی دسته‌جمعی')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([

                        Forms\Components\TextInput::make('settlement_number')
                            ->label('شماره تسویه')
                            ->required(),

                    ])
                    ->action(function ($records, array $data) {

                        foreach ($records as $record) {

                            if ($record->status !== 'pending') {
                                continue;
                            }

                            $record->update([
                                'status' => 'paid',
                                'settlement_number' => $data['settlement_number'],
                                'paid_at' => now(),
                            ]);
                        }

                        Notification::make()
                            ->title('تسویه‌ی دسته‌جمعی انجام شد.')
                            ->success()
                            ->send();
                    }),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherEarnings::route('/'),
            'view' => Pages\ViewTeacherEarning::route('/{record}'),
            'edit' => Pages\EditTeacherEarning::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['teacher.teacherProfile', 'purchase', 'purchaseItem']);
    }
}
