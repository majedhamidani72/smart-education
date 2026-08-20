<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertisementResource\Pages;
use App\Filament\Forms\Components\JalaliDateTimePicker;
use App\Models\Advertisement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * تبلیغات
 * --------------------------------------------------------------------
 * مدیریت بنرهای تبلیغاتی که در جایگاه‌های مختلف اپلیکیشن
 * (صفحه‌ی اصلی، کتاب، درس، آزمون، پروفایل) نمایش داده می‌شوند.
 */
class AdvertisementResource extends Resource
{
    protected static ?string $model = Advertisement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'مدیریت سیستم';

    protected static ?string $navigationLabel = 'تبلیغات';

    protected static ?string $modelLabel = 'تبلیغ';

    protected static ?string $pluralModelLabel = 'تبلیغات';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('SuperAdmin') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('SuperAdmin') ?? false;
    }


    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('title')
                ->label('عنوان تبلیغ')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('image')
                ->label('تصویر بنر')
                ->disk('public')
                ->directory('advertisements')
                ->image()
                ->openable()
                ->required()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('link')
                ->label('لینک مقصد (اختیاری)')
                ->url()
                ->maxLength(255)
                ->helperText('وقتی کاربر روی تبلیغ کلیک می‌کند، به این آدرس هدایت می‌شود. خالی بگذارید اگر فقط جنبه‌ی نمایشی دارد.')
                ->columnSpanFull(),

            Forms\Components\Textarea::make('description')
                ->label('توضیحات داخلی (اختیاری)')
                ->rows(2)
                ->helperText('فقط برای خودتان — به کاربر نمایش داده نمی‌شود.')
                ->columnSpanFull(),

            Forms\Components\Select::make('position')
                ->label('جایگاه نمایش')
                ->options([
                    'home' => 'صفحه‌ی اصلی',
                    'book' => 'صفحه‌ی کتاب',
                    'lesson' => 'صفحه‌ی درس',
                    'quiz' => 'صفحه‌ی آزمون',
                    'profile' => 'پروفایل کاربر',
                ])
                ->required(),

            Forms\Components\TextInput::make('sort_order')
                ->label('ترتیب نمایش')
                ->numeric()
                ->minValue(1)
                ->default(1)
                ->required(),

            JalaliDateTimePicker::make('starts_at')
                ->label('شروع نمایش (اختیاری)')
                ->helperText('خالی بگذارید یعنی همین الان.'),

            JalaliDateTimePicker::make('expires_at')
                ->label('پایان نمایش (اختیاری)')
                ->helperText('خالی بگذارید یعنی تا وقتی که دستی غیرفعالش نکنی.'),

            Forms\Components\Toggle::make('is_active')
                ->label('فعال')
                ->default(true),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('sort_order')

            ->columns([

                Tables\Columns\ImageColumn::make('image')
                    ->label('تصویر')
                    ->disk('public'),

                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('position')
                    ->label('جایگاه')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'home' => 'صفحه‌ی اصلی',
                        'book' => 'کتاب',
                        'lesson' => 'درس',
                        'quiz' => 'آزمون',
                        'profile' => 'پروفایل',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('تعداد بازدید')
                    ->counts('views'),

                Tables\Columns\TextColumn::make('clicks_count')
                    ->label('تعداد کلیک')
                    ->counts('clicks'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتیب')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('شروع')
                    ->formatStateUsing(fn($state) => $state ? \App\Support\Jalali::format($state) : '—'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('پایان')
                    ->formatStateUsing(fn($state) => $state ? \App\Support\Jalali::format($state) : '—'),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('position')
                    ->label('جایگاه')
                    ->options([
                        'home' => 'صفحه‌ی اصلی',
                        'book' => 'کتاب',
                        'lesson' => 'درس',
                        'quiz' => 'آزمون',
                        'profile' => 'پروفایل',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('فعال'),

                Tables\Filters\TrashedFilter::make(),

            ])

            ->actions([

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),

                Tables\Actions\RestoreAction::make(),

                Tables\Actions\ForceDeleteAction::make(),

            ])

            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\RestoreBulkAction::make(),

                    Tables\Actions\ForceDeleteBulkAction::make(),

                ]),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdvertisements::route('/'),
            'create' => Pages\CreateAdvertisement::route('/create'),
            'edit' => Pages\EditAdvertisement::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->withCount(['views', 'clicks']);
    }
}
