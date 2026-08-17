<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentItemResource\Pages;
use App\Models\App;
use App\Models\AppGradeSubject;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\ContentItem;
use App\Models\ContentType;
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

class ContentItemResource extends Resource
{
    use FiltersByTeacherAssignment;

    protected static ?string $model = ContentItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'مدیریت آموزش';

    protected static ?string $navigationLabel = 'محتوای آموزشی';

    protected static ?string $modelLabel = 'محتوای آموزشی';

    protected static ?string $pluralModelLabel = 'محتوای آموزشی';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $isTeacher = auth()->user()?->hasRole('Teacher');

        return $form->schema([

            Forms\Components\Section::make('اطلاعات آموزشی')

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
                        ->required()
                        ->afterStateUpdated(function (Set $set) {

                            $set('grade_id', null);
                            $set('subject_id', null);
                            $set('book_id', null);
                            $set('chapter_id', null);
                            $set('section_id', null);
                        }),

                    Forms\Components\Select::make('grade_id')
                        ->label('پایه')
                        ->options(function (Get $get) {

                            if (! $get('app_id')) {
                                return [];
                            }

                            return Grade::query()
                                ->whereHas(
                                    'appGradeSubjects',
                                    fn($query) =>
                                    $query->where(
                                        'app_id',
                                        $get('app_id')
                                    )
                                )
                                ->orderBy('grade_number')
                                ->pluck(
                                    'title',
                                    'id'
                                );
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->dehydrated(false)
                        ->required()
                        ->afterStateUpdated(function (Set $set) {

                            $set('subject_id', null);
                            $set('book_id', null);
                            $set('chapter_id', null);
                            $set('section_id', null);
                        }),

                    Forms\Components\Select::make('subject_id')
                        ->label('درس')
                        ->options(function (Get $get) {

                            if (! $get('grade_id')) {
                                return [];
                            }

                            return Subject::query()
                                ->whereHas(
                                    'appGradeSubjects',
                                    fn($query) =>
                                    $query
                                        ->where(
                                            'app_id',
                                            $get('app_id')
                                        )
                                        ->where(
                                            'grade_id',
                                            $get('grade_id')
                                        )
                                )
                                ->orderBy('sort_order')
                                ->pluck(
                                    'title',
                                    'id'
                                );
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->dehydrated(false)
                        ->required()
                        ->afterStateUpdated(function (Set $set) {

                            $set('book_id', null);
                            $set('chapter_id', null);
                            $set('section_id', null);
                        }),

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
                                ->where(
                                    'app_grade_subject_id',
                                    $appGradeSubject->id
                                )
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->pluck(
                                    'title',
                                    'id'
                                );
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->dehydrated(false)
                        ->required()
                        ->afterStateUpdated(function (Set $set) {

                            $set('chapter_id', null);
                            $set('section_id', null);
                        }),
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
                        ->live()
                        ->dehydrated(false)
                        ->required()
                        ->createOptionForm([

                            Forms\Components\TextInput::make('title')
                                ->label('نام فصل')
                                ->required(),

                            Forms\Components\Hidden::make('slug'),

                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->default(1),

                            Forms\Components\Toggle::make('is_active')
                                ->default(true),

                        ])
                        ->createOptionUsing(function (array $data, Get $get) {

                            $chapter = Chapter::create([

                                'book_id' => $get('book_id'),

                                'title' => $data['title'],

                                'slug' => Str::slug($data['title']),

                                'sort_order' => $data['sort_order'],

                                'is_active' => $data['is_active'],

                            ]);

                            return $chapter->id;
                        })
                        ->afterStateUpdated(function (Set $set) {

                            $set('section_id', null);
                        }),

                    Forms\Components\Select::make('section_id')
                        ->label('بخش (اختیاری)')
                        ->options(function (Get $get) {

                            if (! $get('chapter_id')) {
                                return [];
                            }

                            return Section::query()
                                ->where('chapter_id', $get('chapter_id'))
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->createOptionForm([

                            Forms\Components\TextInput::make('title')
                                ->label('نام بخش')
                                ->required(),

                            Forms\Components\Hidden::make('slug'),

                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->default(1),

                            Forms\Components\Toggle::make('is_active')
                                ->default(true),

                        ])
                        ->createOptionUsing(function (array $data, Get $get) {

                            $section = Section::create([

                                'chapter_id' => $get('chapter_id'),

                                'title' => $data['title'],

                                'slug' => Str::slug($data['title']),

                                'sort_order' => $data['sort_order'],

                                'is_active' => $data['is_active'],

                            ]);

                            return $section->id;
                        }),

                    Forms\Components\Select::make('content_type_id')
                        ->label('نوع محتوا')
                        ->relationship(
                            'contentType',
                            'title'
                        )
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->label('عنوان')
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

                    Forms\Components\Textarea::make('description')
                        ->label('توضیحات')
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('page_number')
                        ->label('شماره صفحه')
                        ->numeric(),

                    Forms\Components\Toggle::make('is_free')
                        ->label('رایگان')
                        ->default(false),

                    Forms\Components\Hidden::make('created_by')
                        ->default(fn() => auth()->id()),

                    Forms\Components\Hidden::make('status')
                        ->default('pending'),

                    Forms\Components\Hidden::make('sort_order')
                        ->default(1),

                ]),
            Forms\Components\Section::make('اطلاعات اختصاصی محتوا')

                ->columns(2)

                ->schema([

                    /*
                    |--------------------------------------------------------------------------
                    | تدریس
                    |--------------------------------------------------------------------------
                    */

                    Forms\Components\TextInput::make('video.title')
                        ->label('عنوان ویدئو')
                        ->visible(function (Get $get) {

                            return ContentType::query()
                                ->whereKey($get('content_type_id'))
                                ->value('slug') === 'video';
                        }),

                    Forms\Components\FileUpload::make('video.video_file')
                        ->label('فایل ویدئو')
                        ->directory('videos')
                        ->acceptedFileTypes([
                            'video/mp4',
                            'video/x-msvideo',
                            'video/quicktime',
                            'video/x-matroska',
                        ])
                        ->downloadable()
                        ->openable()
                        ->visible(function (Get $get) {

                            return ContentType::query()
                                ->whereKey($get('content_type_id'))
                                ->value('slug') === 'video';
                        }),

                    /*
                    |--------------------------------------------------------------------------
                    | گام به گام
                    |--------------------------------------------------------------------------
                    */

                    Forms\Components\Repeater::make('stepByStep')

                        ->label('صفحات گام به گام')

                        ->collapsed()

                        ->defaultItems(1)

                        ->reorderable()

                        ->cloneable()

                        ->addActionLabel('افزودن صفحه')

                        ->visible(function (Get $get) {

                            return ContentType::query()
                                ->whereKey($get('content_type_id'))
                                ->value('slug') === 'step_by_step';
                        })

                        ->schema([

                            Forms\Components\TextInput::make('title')
                                ->label('عنوان صفحه')
                                ->required(),

                            Forms\Components\FileUpload::make('image')
                                ->label('تصویر')
                                ->directory('step-by-step')
                                ->image()
                                ->required(),

                            Forms\Components\TextInput::make('sort_order')
                                ->label('ترتیب')
                                ->numeric()
                                ->default(1),

                        ]),

                    /*
                    |--------------------------------------------------------------------------
                    | نمونه سوال
                    |--------------------------------------------------------------------------
                    */

                    Forms\Components\TextInput::make('pdfFile.title')
                        ->label('عنوان فایل PDF')
                        ->visible(function (Get $get) {

                            return ContentType::query()
                                ->whereKey($get('content_type_id'))
                                ->value('slug') === 'sample_question';
                        }),

                    Forms\Components\FileUpload::make('pdfFile.file')

                        ->label('فایل PDF')

                        ->directory('sample-questions')

                        ->acceptedFileTypes([
                            'application/pdf',
                        ])

                        ->downloadable()

                        ->openable()

                        ->visible(function (Get $get) {

                            return ContentType::query()
                                ->whereKey($get('content_type_id'))
                                ->value('slug') === 'sample_question';
                        }),

                ]),

            Forms\Components\Section::make('مدیریت محتوا')

                ->columns(2)

                ->visible(fn() => ! $isTeacher)

                ->schema([

                    Forms\Components\Select::make('status')

                        ->label('وضعیت')

                        ->options([

                            'pending' => 'در انتظار بررسی',

                            'approved' => 'تأیید شده',

                            'rejected' => 'رد شده',

                            'published' => 'منتشر شده',

                        ])

                        ->required(),

                    Forms\Components\Textarea::make('rejection_reason')

                        ->label('دلیل رد')

                        ->rows(3)

                        ->visible(
                            fn(Get $get) =>

                            $get('status') === 'rejected'
                        ),

                    Forms\Components\DateTimePicker::make('published_at')

                        ->label('زمان انتشار'),

                    Forms\Components\TextInput::make('sort_order')

                        ->label('ترتیب نمایش')

                        ->numeric()

                        ->default(1),

                ]),

        ]);
    }
    public static function table(Table $table): Table
    {
        $isTeacher = auth()->user()?->hasRole('Teacher');

        return $table

            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'section.chapter.book.appGradeSubject.app.title'
                )
                    ->label('اپلیکیشن')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'section.chapter.book.appGradeSubject.grade.title'
                )
                    ->label('پایه')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'section.chapter.book.appGradeSubject.subject.title'
                )
                    ->label('درس')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'section.chapter.book.title'
                )
                    ->label('کتاب')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'section.chapter.title'
                )
                    ->label('فصل')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'section.title'
                )
                    ->label('بخش')
                    ->placeholder('بدون بخش')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make(
                    'contentType.title'
                )
                    ->label('نوع محتوا')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('page_number')
                    ->label('صفحه')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_free')
                    ->label('رایگان')
                    ->boolean(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('وضعیت')
                    ->colors([

                        'warning' => 'pending',

                        'success' => 'approved',

                        'danger' => 'rejected',

                        'primary' => 'published',

                    ])
                    ->formatStateUsing(fn($state) => match ($state) {

                        'pending' => 'در انتظار بررسی',

                        'approved' => 'تأیید شده',

                        'rejected' => 'رد شده',

                        'published' => 'منتشر شده',

                        default => $state,
                    })
                    ->visible(fn() => ! $isTeacher),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('ایجادکننده')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('بررسی‌کننده')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => ! $isTeacher),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),

            ])

            ->filters([

                Tables\Filters\TernaryFilter::make('is_free')
                    ->label('رایگان'),

                Tables\Filters\SelectFilter::make('content_type_id')
                    ->label('نوع محتوا')
                    ->relationship(
                        'contentType',
                        'title'
                    ),

                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([

                        'pending' => 'در انتظار بررسی',

                        'approved' => 'تأیید شده',

                        'rejected' => 'رد شده',

                        'published' => 'منتشر شده',

                    ])
                    ->visible(fn() => ! $isTeacher),

            ])

            ->actions([

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),

            ])

            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\DeleteBulkAction::make(),

                ]),

            ]);
    }
    public static function getRelations(): array
    {
        return [

            //
            // در آینده RelationManager ها
            // مانند VideoRelationManager
            // PdfRelationManager
            // SampleQuestionRelationManager
            //

        ];
    }

    public static function getPages(): array
    {
        return [

            'index' => Pages\ListContentItems::route('/'),

            'create' => Pages\CreateContentItem::route('/create'),

            'edit' => Pages\EditContentItem::route('/{record}/edit'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()

            ->withoutGlobalScopes([

                SoftDeletingScope::class,

            ])

            ->with([

                'contentType',

                'creator',

                'reviewer',

                'section',

                'section.chapter',

                'section.chapter.book',

                'section.chapter.book.appGradeSubject',

                'section.chapter.book.appGradeSubject.app',

                'section.chapter.book.appGradeSubject.grade',

                'section.chapter.book.appGradeSubject.subject',

            ]);

        return static::applyTeacherFilter(

            $query,

            'section.chapter.book.teacherAssignments'

        );
    }
}
