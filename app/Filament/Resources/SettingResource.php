<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'مدیریت سیستم';

    protected static ?string $navigationLabel = 'تنظیمات سیستم';

    protected static ?string $modelLabel = 'تنظیم';

    protected static ?string $pluralModelLabel = 'تنظیمات سیستم';

    protected static ?int $navigationSort = 100;



    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check()
            && auth()->user()->hasRole('SuperAdmin');
    }



    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('SuperAdmin') ?? false;
    }



    public static function canCreate(): bool
    {
        return false;
    }



    public static function canCreateAnother(): bool
    {
        return false;
    }



    public static function canEdit(
        $record
    ): bool {

        return auth()->user()?->hasRole('SuperAdmin') ?? false;
    }



    public static function canDelete(
        $record
    ): bool {

        return false;
    }



    public static function canDeleteAny(): bool
    {
        return false;
    }



    public static function form(
        Form $form
    ): Form {

        return $form->schema([

            /*
            |--------------------------------------------------------------------------
            | شناسه داخلی تنظیم
            |--------------------------------------------------------------------------
            */

            Hidden::make('key'),

            /*
            |--------------------------------------------------------------------------
            | عنوان تنظیم
            |--------------------------------------------------------------------------
            */

            Forms\Components\TextInput::make('description')

                ->label('عنوان')

                ->disabled()

                ->dehydrated(false)

                ->columnSpanFull(),

            /*
            |--------------------------------------------------------------------------
            | مقدار تنظیم
            |--------------------------------------------------------------------------
            */

            Textarea::make('value')

                ->label('مقدار تنظیم')

                ->rows(15)

                ->required()

                ->columnSpanFull(),

        ]);
    }
    public static function table(
        Table $table
    ): Table {

        return $table

            ->defaultSort('id')

            ->striped()

            ->columns([

                Tables\Columns\TextColumn::make('description')

                    ->label('عنوان تنظیم')

                    ->weight('bold')

                    ->searchable()

                    ->wrap(),

                Tables\Columns\TextColumn::make('value')

                    ->label('مقدار')

                    ->limit(80)

                    ->wrap(),

                Tables\Columns\TextColumn::make('updated_at')

                    ->label('آخرین بروزرسانی')

                    ->dateTime('Y-m-d H:i')

                    ->sortable(),

            ])

            ->filters([])

            ->actions([

                Tables\Actions\EditAction::make()

                    ->label('ویرایش')

                    ->icon('heroicon-o-pencil-square'),

            ])

            ->bulkActions([]);
    }



    public static function getRelations(): array
    {
        return [];
    }



    public static function getPages(): array
    {
        return [

            'index' => Pages\ListSettings::route('/'),

            'create' => Pages\CreateSetting::route('/create'),

            'edit' => Pages\EditSetting::route('/{record}/edit'),

        ];
    }



    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }



    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
