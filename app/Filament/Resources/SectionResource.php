<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Models\Section;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'مدیریت آموزش';

    protected static ?string $navigationLabel = 'بخش‌ها';

    protected static ?string $modelLabel = 'بخش';

    protected static ?string $pluralModelLabel = 'بخش‌ها';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Select::make('chapter_id')
                ->label('فصل')
                ->relationship('chapter', 'title')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('title')
                ->label('عنوان بخش')
                ->required()
                ->maxLength(255),

            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Textarea::make('description')
                ->label('توضیحات')
                ->rows(4)
                ->columnSpanFull(),

            TextInput::make('sort_order')
                ->label('ترتیب نمایش')
                ->numeric()
                ->default(1)
                ->required(),

            Toggle::make('is_active')
                ->label('فعال')
                ->default(true),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('sort_order')

            ->columns([

                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('chapter.title')
                    ->label('فصل')
                    ->searchable(),

                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('ترتیب')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('ایجاد')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),

            ])

            ->filters([

                TernaryFilter::make('is_active')
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

            'index' => Pages\ListSections::route('/'),

            'create' => Pages\CreateSection::route('/create'),

            'edit' => Pages\EditSection::route('/{record}/edit'),

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
