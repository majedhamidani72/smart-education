<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeResource\Pages;
use App\Models\Grade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'مدیریت آموزش';

    protected static ?string $navigationLabel = 'پایه‌ها';

    /**
     * این Resource دیگر در منوی بیرونی نمایش داده نمی‌شود.
     * ایجاد/ویرایش پایه از داخل فرم «ایجاد محتوای آموزشی»
     * (ContentItemResource) انجام می‌گیرد. صفحات این Resource
     * همچنان در دسترس‌اند (برای لینک‌های داخلی احتمالی)، فقط
     * از سایدبار حذف شده‌اند.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $modelLabel = 'پایه';

    protected static ?string $pluralModelLabel = 'پایه‌ها';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('title')
                ->label('نام پایه')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set(
                    'slug',
                    Str::slug($state)
                )),

            Forms\Components\Hidden::make('slug'),

            Forms\Components\TextInput::make('grade_number')
                ->label('شماره پایه')
                ->numeric()
                ->required()
                ->minValue(1)
                ->maxValue(12)
                ->unique(ignoreRecord: true),

            Forms\Components\Toggle::make('is_active')
                ->label('فعال')
                ->default(true),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('grade_number')

            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('نام پایه')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grade_number')
                    ->label('شماره پایه')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),

            ])

            ->filters([

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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [

            'index' => Pages\ListGrades::route('/'),

            'create' => Pages\CreateGrade::route('/create'),

            'edit' => Pages\EditGrade::route('/{record}/edit'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()

            ->withoutGlobalScopes([

                SoftDeletingScope::class,

            ]);
    }
}
