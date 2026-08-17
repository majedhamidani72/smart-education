<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\App;
use App\Models\AppGradeSubject;
use App\Models\Book;
use App\Models\Grade;
use App\Models\Subject;
use App\Traits\FiltersByTeacherAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookResource extends Resource
{
    use FiltersByTeacherAssignment;

    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'مدیریت آموزش';

    protected static ?string $navigationLabel = 'کتاب‌ها';

    protected static ?string $modelLabel = 'کتاب';

    protected static ?string $pluralModelLabel = 'کتاب‌ها';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('اطلاعات کتاب')
                ->columns(2)
                ->schema([

                    Forms\Components\Select::make('app_id')
                        ->label('اپلیکیشن')
                        ->options(
                            App::query()
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->pluck('title', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(function (Set $set) {

                            $set('grade_id', null);
                            $set('subject_id', null);
                            $set('app_grade_subject_id', null);

                        })
                        ->required(),

                    Forms\Components\Select::make('grade_id')
                        ->label('پایه')
                        ->options(function (Get $get) {

                            if (! $get('app_id')) {
                                return [];
                            }

                            return Grade::query()
                                ->whereHas(
                                    'appGradeSubjects',
                                    fn ($query) => $query
                                        ->where('app_id', $get('app_id'))
                                )
                                ->orderBy('grade_number')
                                ->pluck('title', 'id');

                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(function (Set $set) {

                            $set('subject_id', null);
                            $set('app_grade_subject_id', null);

                        })
                        ->required(),

                    Forms\Components\Select::make('subject_id')
                        ->label('درس')
                        ->options(function (Get $get) {

                            if (! $get('grade_id')) {
                                return [];
                            }

                            return Subject::query()
                                ->whereHas(
                                    'appGradeSubjects',
                                    fn ($query) => $query
                                        ->where('app_id', $get('app_id'))
                                        ->where('grade_id', $get('grade_id'))
                                )
                                ->orderBy('sort_order')
                                ->pluck('title', 'id');

                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {

                            $record = AppGradeSubject::query()
                                ->where('app_id', $get('app_id'))
                                ->where('grade_id', $get('grade_id'))
                                ->where('subject_id', $state)
                                ->first();

                            $set(
                                'app_grade_subject_id',
                                $record?->id
                            );

                        })
                        ->required(),

                    Forms\Components\Hidden::make('app_grade_subject_id')
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->label('نام کتاب')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Set $set) {

                            $set(
                                'slug',
                                Str::slug($state)
                            );

                        }),

                    Forms\Components\Hidden::make('slug'),

                    Forms\Components\FileUpload::make('cover')
                        ->label('تصویر جلد')
                        ->directory('books')
                        ->image(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('فعال')
                        ->default(true),

                ]),

        ]);
    }
        public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'appGradeSubject.app.title'
                )
                    ->label('اپلیکیشن')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'appGradeSubject.grade.title'
                )
                    ->label('پایه')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'appGradeSubject.subject.title'
                )
                    ->label('درس')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('cover')
                    ->label('جلد'),

                Tables\Columns\TextColumn::make('title')
                    ->label('نام کتاب')
                    ->searchable()
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

                Tables\Filters\SelectFilter::make('app_grade_subject_id')
                    ->label('اپلیکیشن')
                    ->relationship(
                        'appGradeSubject.app',
                        'title'
                    ),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('فعال'),

                Tables\Filters\TrashedFilter::make(),

            ])

            ->actions([

                Tables\Actions\ViewAction::make(),

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

            'index' => Pages\ListBooks::route('/'),

            'create' => Pages\CreateBook::route('/create'),

            'edit' => Pages\EditBook::route('/{record}/edit'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()

            ->withoutGlobalScopes([

                SoftDeletingScope::class,

            ]);

        return static::applyTeacherFilter(
            $query
        );
    }
}
