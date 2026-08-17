<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Models\App;
use App\Models\AppGradeSubject;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Subject;
use App\Traits\FiltersByTeacherAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class SectionResource extends Resource
{
    use FiltersByTeacherAssignment;

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

            Forms\Components\Section::make('اطلاعات بخش')
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
                            $set('book_id', null);
                            $set('chapter_id', null);
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
                                    fn($query) => $query
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
                            $set('book_id', null);
                            $set('chapter_id', null);
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
                                    fn($query) => $query
                                        ->where('app_id', $get('app_id'))
                                        ->where('grade_id', $get('grade_id'))
                                )
                                ->orderBy('title')
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(function (Set $set) {

                            $set('book_id', null);
                            $set('chapter_id', null);
                        })
                        ->required(),

                    Forms\Components\Select::make('book_id')
                        ->label('کتاب')
                        ->options(function (Get $get) {

                            if (! $get('subject_id')) {
                                return [];
                            }

                            $appGradeSubject = AppGradeSubject::query()
                                ->where('app_id', $get('app_id'))
                                ->where('grade_id', $get('grade_id'))
                                ->where('subject_id', $get('subject_id'))
                                ->first();

                            if (! $appGradeSubject) {
                                return [];
                            }

                            return Book::query()
                                ->where('app_grade_subject_id', $appGradeSubject->id)
                                ->where('is_active', true)
                                ->orderBy('title')
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(function (Set $set) {

                            $set('chapter_id', null);
                        })
                        ->required(),

                    Forms\Components\Select::make('chapter_id')
                        ->label('فصل')
                        ->options(function (Get $get) {

                            if (! $get('book_id')) {
                                return [];
                            }

                            return Chapter::query()
                                ->where('book_id', $get('book_id'))
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->label('نام بخش')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Set $set) {

                            $set('slug', Str::slug($state));
                        }),

                    Forms\Components\Hidden::make('slug'),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('ترتیب نمایش')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('فعال')
                        ->default(true),

                ]),

        ]);
    }
    public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('sort_order')

            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'chapter.book.appGradeSubject.app.title'
                )
                    ->label('اپلیکیشن')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'chapter.book.appGradeSubject.grade.title'
                )
                    ->label('پایه')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'chapter.book.appGradeSubject.subject.title'
                )
                    ->label('درس')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'chapter.book.title'
                )
                    ->label('کتاب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'chapter.title'
                )
                    ->label('فصل')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('نام بخش')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتیب')
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

                Tables\Filters\SelectFilter::make('chapter_id')
                    ->label('فصل')
                    ->relationship(
                        'chapter',
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

            'index' => Pages\ListSections::route('/'),

            'create' => Pages\CreateSection::route('/create'),

            'edit' => Pages\EditSection::route('/{record}/edit'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()

            ->withoutGlobalScopes([

                SoftDeletingScope::class,

            ]);

        return static::applyTeacherFilter(
            $query,
            'chapter.book.teacherAssignments'
        );
    }
}
