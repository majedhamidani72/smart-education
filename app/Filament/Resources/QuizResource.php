<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Filament\Resources\QuizResource\RelationManagers\QuestionsRelationManager;
use App\Filament\Forms\Components\JalaliDateTimePicker;

use App\Models\App;
use App\Models\AppGradeSubject;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Grade;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\Subject;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;

use Filament\Resources\Resource;

use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'آزمون آنلاین';

    protected static ?string $navigationLabel = 'آزمون‌ها';

    protected static ?string $modelLabel = 'آزمون';

    protected static ?string $pluralModelLabel = 'آزمون‌ها';

    protected static ?int $navigationSort = 1;

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    /**
     * کتاب واقعی پشت هر quizable را برمی‌گرداند (چه خودِ کتاب باشد،
     * چه فصل، چه بخش) — برای پیدا کردن پایه/درس/نوع ساختار آزمون
     * صرف‌نظر از سطح آزمون.
     */
    protected static function resolveBook($record): ?Book
    {
        return match ($record->quizable_type) {

            Book::class => $record->quizable,

            Chapter::class => $record->quizable?->book,

            Section::class => $record->quizable?->chapter?->book,

            default => null,
        };
    }

    protected static function resolveGradeTitle($record): string
    {
        return static::resolveBook($record)?->appGradeSubject?->grade?->title ?? '—';
    }

    protected static function resolveSubjectTitle($record): string
    {
        return static::resolveBook($record)?->appGradeSubject?->subject?->title ?? '—';
    }

    protected static function resolveExamStructure($record): string
    {
        return static::resolveBook($record)?->appGradeSubject?->subject?->exam_structure
            ?? 'chapter_section';
    }

    /**
     * یک رنگ ثابت و همیشه‌یکسان برای یک برچسب مشخص برمی‌گرداند —
     * یعنی مثلاً «پایه پنجم» همیشه همان رنگ را می‌گیرد، هر جای
     * جدول که باشد، بدون این‌که از قبل رنگ هر پایه را دستی تعریف
     * کرده باشیم.
     */
    protected static function colorForLabel(?string $label): string
    {
        $palette = ['primary', 'success', 'warning', 'danger', 'info', 'gray'];

        if (blank($label) || $label === '—') {
            return 'gray';
        }

        $index = crc32($label) % count($palette);

        return $palette[$index];
    }

    /**
     * کتاب‌هایی که معلم فعلی به آن‌ها دسترسی دارد (همان منطق
     * ContentItemResource::teacherAssignedBooks). برای محدود کردن
     * فیلدهای اپلیکیشن/پایه/درس/کتاب به دامنه‌ی واقعی معلم —
     * حتی اگر معلم به چند پایه یا چند کتاب مختلف دسترسی داشته
     * باشد.
     */
    protected static function teacherAssignedBooks(): \Illuminate\Support\Collection
    {
        $bookIds = \App\Models\TeacherAssignment::query()
            ->where('teacher_id', auth()->id())
            ->where('is_active', true)
            ->pluck('book_id');

        return Book::query()
            ->whereIn('id', $bookIds)
            ->where('is_active', true)
            ->with('appGradeSubject')
            ->get();
    }

    public static function form(Form $form): Form
    {
        $isTeacher = auth()->user()?->hasRole('Teacher');

        $teacherBooks = $isTeacher
            ? static::teacherAssignedBooks()
            : collect();
        return $form->schema([

            Forms\Components\TextInput::make('title')
                ->label('عنوان آزمون')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('description')
                ->label('توضیحات آزمون')
                ->rows(4)
                ->columnSpanFull(),

            /*
            |--------------------------------------------------------------------------
            | مسیر آموزشی — تعیین می‌کند این آزمون برای کدام کتاب است
            |--------------------------------------------------------------------------
            | قبلاً فیلد «انتخاب مورد آزمون» مستقیم لیست همه‌ی
            | فصل‌ها/بخش‌های کل دیتابیس را نشان می‌داد، بدون این‌که
            | مشخص باشد مربوط به کدام کتاب/پایه است. حالا اول باید
            | کتاب مشخص شود.
            */

            Forms\Components\Section::make('مسیر آموزشی')

                ->columns(4)

                ->schema([

                    Forms\Components\Select::make('app_id')
                        ->label('اپلیکیشن')
                        ->options(function () use ($isTeacher, $teacherBooks) {

                            if ($isTeacher) {
                                return $teacherBooks
                                    ->pluck('appGradeSubject.app_id')
                                    ->unique()
                                    ->mapWithKeys(fn($appId) => [
                                        $appId => App::find($appId)?->title,
                                    ]);
                            }

                            return App::query()
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->getOptionLabelUsing(fn($value) => App::find($value)?->title)
                        ->live()
                        ->required()
                        ->dehydrated(false)
                        ->default(function () use ($isTeacher, $teacherBooks) {

                            if (! $isTeacher) {
                                return null;
                            }

                            $appIds = $teacherBooks->pluck('appGradeSubject.app_id')->unique();

                            return $appIds->count() === 1 ? $appIds->first() : null;
                        })
                        ->afterStateUpdated(function (Set $set) {

                            $set('grade_id', null);
                            $set('subject_id', null);
                            $set('book_id', null);
                            $set('quizable_type', null);
                            $set('quizable_id', null);
                        }),

                    Forms\Components\Select::make('grade_id')
                        ->label('پایه')
                        ->options(function (Get $get) use ($isTeacher, $teacherBooks) {

                            if ($isTeacher) {
                                return $teacherBooks
                                    ->when(
                                        $get('app_id'),
                                        fn($collection) => $collection->filter(
                                            fn($book) =>
                                            $book->appGradeSubject?->app_id == $get('app_id')
                                        )
                                    )
                                    ->pluck('appGradeSubject.grade_id')
                                    ->unique()
                                    ->mapWithKeys(fn($gradeId) => [
                                        $gradeId => Grade::find($gradeId)?->title,
                                    ]);
                            }

                            if (! $get('app_id')) {
                                return [];
                            }

                            return Grade::query()
                                ->whereHas(
                                    'appGradeSubjects',
                                    fn($query) => $query->where('app_id', $get('app_id'))
                                )
                                ->orderBy('grade_number')
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->getOptionLabelUsing(fn($value) => Grade::find($value)?->title)
                        ->live()
                        ->required()
                        ->dehydrated(false)
                        ->default(function () use ($isTeacher, $teacherBooks) {

                            if (! $isTeacher) {
                                return null;
                            }

                            $gradeIds = $teacherBooks->pluck('appGradeSubject.grade_id')->unique();

                            return $gradeIds->count() === 1 ? $gradeIds->first() : null;
                        })
                        ->afterStateUpdated(function (Set $set) {

                            $set('subject_id', null);
                            $set('book_id', null);
                            $set('quizable_type', null);
                            $set('quizable_id', null);
                        }),

                    Forms\Components\Select::make('subject_id')
                        ->label('درس')
                        ->options(function (Get $get) use ($isTeacher, $teacherBooks) {

                            if ($isTeacher) {
                                return $teacherBooks
                                    ->when(
                                        $get('grade_id'),
                                        fn($collection) => $collection->filter(
                                            fn($book) =>
                                            $book->appGradeSubject?->app_id == $get('app_id')
                                            && $book->appGradeSubject?->grade_id == $get('grade_id')
                                        )
                                    )
                                    ->pluck('appGradeSubject.subject_id')
                                    ->unique()
                                    ->mapWithKeys(fn($subjectId) => [
                                        $subjectId => Subject::find($subjectId)?->title,
                                    ]);
                            }

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
                                ->orderBy('sort_order')
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->getOptionLabelUsing(fn($value) => Subject::find($value)?->title)
                        ->live()
                        ->required()
                        ->dehydrated(false)
                        ->default(function () use ($isTeacher, $teacherBooks) {

                            if (! $isTeacher) {
                                return null;
                            }

                            $subjectIds = $teacherBooks->pluck('appGradeSubject.subject_id')->unique();

                            return $subjectIds->count() === 1 ? $subjectIds->first() : null;
                        })
                        // با عوض‌شدن درس، «نوع ساختار آزمون» هم ممکن
                        // است عوض شود (مثلاً از ریاضی به فارسی).
                        ->afterStateUpdated(function (Set $set) {

                            $set('book_id', null);
                            $set('quizable_type', null);
                            $set('quizable_id', null);
                        }),

                    Forms\Components\Select::make('book_id')
                        ->label('کتاب')
                        ->options(function (Get $get) use ($isTeacher, $teacherBooks) {

                            if ($isTeacher) {
                                return $teacherBooks
                                    ->when(
                                        $get('subject_id'),
                                        fn($collection) => $collection->filter(
                                            fn($book) =>
                                            $book->appGradeSubject?->app_id == $get('app_id')
                                            && $book->appGradeSubject?->grade_id == $get('grade_id')
                                            && $book->appGradeSubject?->subject_id == $get('subject_id')
                                        )
                                    )
                                    ->pluck('title', 'id');
                            }

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
                                ->orderBy('sort_order')
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->getOptionLabelUsing(fn($value) => Book::find($value)?->title)
                        ->live()
                        ->required()
                        ->dehydrated(false)
                        ->default(function () use ($isTeacher, $teacherBooks) {

                            return $isTeacher && $teacherBooks->count() === 1
                                ? $teacherBooks->first()->id
                                : null;
                        })
                        ->afterStateUpdated(function (Set $set) {

                            $set('quizable_type', null);
                            $set('quizable_id', null);
                        }),

                ]),

            /*
            |--------------------------------------------------------------------------
            | نوع آزمون
            |--------------------------------------------------------------------------
            | گزینه‌ها بر اساس «نوع ساختار آزمون» همان درس تغییر
            | می‌کنند: درس‌هایی مثل ریاضی بر اساس فصل/بخش، و
            | درس‌هایی مثل فارسی/مطالعات بر اساس درس + نوبت اول/دوم
            | (طبق آیین‌نامه‌ی رسمی: نوبت اول از نصف کتاب، نوبت دوم
            | از کل کتاب).
            */

            Forms\Components\Select::make('quizable_type')

                ->label('سطح آزمون')

                ->options(function (Get $get) {

                    $examStructure = Subject::find($get('subject_id'))?->exam_structure
                        ?? 'chapter_section';

                    if ($examStructure === 'lesson_term') {

                        return [
                            Chapter::class => 'آزمون بعد از هر درس',
                            Book::class => 'آزمون نوبت (اول یا دوم)',
                        ];
                    }

                    return [
                        Book::class => 'آزمون جامع کتاب',
                        Chapter::class => 'آزمون فصل',
                        Section::class => 'آزمون بخش',
                    ];
                })

                ->live()

                ->required()

                ->disabled(fn(Get $get) => ! $get('book_id'))

                ->afterStateUpdated(

                    function ($state, Set $set, Get $get) {

                        $set('quizable_id', $state === Book::class ? $get('book_id') : null);

                        $set('section_chapter_filter', null);

                        $set('term_scope', null);
                    }

                ),

            /*
            |--------------------------------------------------------------------------
            | نوبت (فقط برای درس‌های «درس و نیم‌سال»، وقتی سطح آزمون
            | «نوبت» انتخاب شده باشد)
            |--------------------------------------------------------------------------
            */

            Forms\Components\Select::make('term_scope')

                ->label('نوبت')

                ->options([
                    1 => 'نوبت اول (نصف کتاب)',
                    2 => 'نوبت دوم / نهایی (کل کتاب)',
                ])

                ->live()

                ->required(
                    fn(Get $get) =>
                    $get('quizable_type') === Book::class
                    && Subject::find($get('subject_id'))?->exam_structure === 'lesson_term'
                )

                ->visible(
                    fn(Get $get) =>
                    $get('quizable_type') === Book::class
                    && Subject::find($get('subject_id'))?->exam_structure === 'lesson_term'
                ),

            /*
            |--------------------------------------------------------------------------
            | فیلتر فصل (فقط وقتی سطح آزمون «بخش» باشد، برای محدود
            | کردن لیست بخش‌ها به همان فصل)
            |--------------------------------------------------------------------------
            */

            Forms\Components\Select::make('section_chapter_filter')

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

                ->live()

                ->dehydrated(false)

                ->visible(fn(Get $get) => $get('quizable_type') === Section::class)

                ->required(fn(Get $get) => $get('quizable_type') === Section::class)

                ->afterStateUpdated(fn(Set $set) => $set('quizable_id', null)),

            /*
            |--------------------------------------------------------------------------
            | انتخاب مورد آزمون
            |--------------------------------------------------------------------------
            | برای «آزمون کتاب/نوبت» این فیلد اصلاً نمایش داده
            | نمی‌شود — چون کتاب از بالا مشخص شده و همان کافی است.
            */

            Forms\Components\Select::make('quizable_id')

                ->label(function (Get $get) {

                    $examStructure = Subject::find($get('subject_id'))?->exam_structure
                        ?? 'chapter_section';

                    return match ($get('quizable_type')) {

                        Chapter::class => $examStructure === 'lesson_term'
                            ? 'درس'
                            : 'فصل',

                        Section::class => 'بخش',

                        default => 'انتخاب مورد آزمون',
                    };
                })

                ->options(function (Get $get) {

                    return match ($get('quizable_type')) {

                        Chapter::class => Chapter::query()
                            ->where('book_id', $get('book_id'))
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('title', 'id'),

                        Section::class => Section::query()
                            ->where('chapter_id', $get('section_chapter_filter'))
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('title', 'id'),

                        default => [],
                    };
                })

                ->searchable()

                ->preload()

                ->live()

                ->visible(
                    fn(Get $get) =>
                    in_array($get('quizable_type'), [Chapter::class, Section::class], true)
                )

                ->required(
                    fn(Get $get) =>
                    in_array($get('quizable_type'), [Chapter::class, Section::class], true)
                ),

            /*
            |--------------------------------------------------------------------------
            | تنظیمات آزمون
            |--------------------------------------------------------------------------
            */

            Forms\Components\TextInput::make('questions_count')
                ->label('تعداد سوال')
                ->numeric()
                ->minValue(1)
                ->default(10)
                ->required(),

            Forms\Components\TextInput::make('time_limit')
                ->label('زمان آزمون (دقیقه)')
                ->numeric()
                ->minValue(0)
                ->default(20),

            Forms\Components\TextInput::make('max_attempts')
                ->label('حداکثر دفعات شرکت')
                ->numeric()
                ->minValue(1)
                ->default(1),

            Forms\Components\Toggle::make('randomize_questions')
                ->label('تصادفی کردن سوالات')
                ->default(true),

            Forms\Components\Toggle::make('randomize_options')
                ->label('تصادفی کردن گزینه‌ها')
                ->default(true),

            Forms\Components\Toggle::make('show_result')
                ->label('نمایش نتیجه')
                ->default(true),

            Forms\Components\Toggle::make('show_correct_answers')
                ->label('نمایش پاسخ صحیح')
                ->default(false),

            Forms\Components\Toggle::make('is_free')
                ->label('رایگان')
                ->default(false),

            Forms\Components\Select::make('status')
                ->label('وضعیت')
                ->options([
                    'draft' => 'پیش نویس',
                    'pending' => 'در انتظار بررسی',
                    'active' => 'فعال',
                    'inactive' => 'غیرفعال',
                ])
                ->default('draft')
                ->required(),

            JalaliDateTimePicker::make('published_at')
                ->label('زمان انتشار')
                ->default(now()->format('Y-m-d H:i:s')),

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                // بر خلاف قبل، این ستون از روی quizable واقعی (که
                // می‌تواند کتاب، فصل، یا بخش باشد) پایه را پیدا
                // می‌کند — قبلاً فقط برای نوع «کتاب» درست کار می‌کرد.
                Tables\Columns\TextColumn::make('grade')
                    ->label('پایه')
                    ->getStateUsing(fn($record) => static::resolveGradeTitle($record))
                    ->badge()
                    ->color(fn($state) => static::colorForLabel($state)),

                Tables\Columns\TextColumn::make('subject')
                    ->label('درس')
                    ->getStateUsing(fn($record) => static::resolveSubjectTitle($record))
                    ->badge()
                    ->color(fn($state) => static::colorForLabel($state)),

                // سطح آزمون (بخش/فصل/کتاب یا بعد از هر درس/نوبت) —
                // رنگش بر اساس نوعِ quizable_type ثابت است، نه
                // هش‌شده، تا مثلاً همیشه «بخش» یک رنگ خاص و «فصل»
                // رنگ دیگری داشته باشد.
                Tables\Columns\BadgeColumn::make('quizable_type')
                    ->label('سطح آزمون')
                    ->colors([
                        'info' => Section::class,
                        'warning' => Chapter::class,
                        'success' => Book::class,
                    ])
                    ->formatStateUsing(function ($state, $record) {

                        $examStructure = $record->quizable
                            ? static::resolveExamStructure($record)
                            : 'chapter_section';

                        if ($state === Section::class) {
                            return 'بخش';
                        }

                        if ($state === Chapter::class) {
                            return $examStructure === 'lesson_term'
                                ? 'بعد از هر درس'
                                : 'فصل';
                        }

                        if ($state === Book::class) {

                            if ($examStructure === 'lesson_term') {
                                return $record->term_scope == 1
                                    ? 'نوبت اول'
                                    : 'نوبت دوم';
                            }

                            return 'آزمون جامع';
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('quizable.title')
                    ->label('کتاب / فصل / بخش')
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان آزمون')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('سازنده'),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('تعداد سوال'),

                Tables\Columns\TextColumn::make('time_limit')
                    ->label('زمان (دقیقه)'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('وضعیت')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending',
                        'success' => 'active',
                        'danger' => 'inactive',
                    ]),

                Tables\Columns\IconColumn::make('is_free')
                    ->label('رایگان')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('ایجاد')
                    ->formatStateUsing(fn($state) => \App\Support\Jalali::format($state)),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('grade_id')
                    ->label('پایه')
                    ->options(Grade::pluck('title', 'id'))
                    ->query(function ($query, array $data) {

                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        $gradeId = $data['value'];

                        return $query->whereHasMorph(
                            'quizable',
                            [Book::class, Chapter::class, Section::class],
                            function ($q, $type) use ($gradeId) {

                                match ($type) {

                                    Book::class => $q->whereHas(
                                        'appGradeSubject',
                                        fn($qq) => $qq->where('grade_id', $gradeId)
                                    ),

                                    Chapter::class => $q->whereHas(
                                        'book.appGradeSubject',
                                        fn($qq) => $qq->where('grade_id', $gradeId)
                                    ),

                                    Section::class => $q->whereHas(
                                        'chapter.book.appGradeSubject',
                                        fn($qq) => $qq->where('grade_id', $gradeId)
                                    ),

                                };
                            }
                        );
                    }),

                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('درس')
                    ->options(Subject::pluck('title', 'id'))
                    ->query(function ($query, array $data) {

                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        $subjectId = $data['value'];

                        return $query->whereHasMorph(
                            'quizable',
                            [Book::class, Chapter::class, Section::class],
                            function ($q, $type) use ($subjectId) {

                                match ($type) {

                                    Book::class => $q->whereHas(
                                        'appGradeSubject',
                                        fn($qq) => $qq->where('subject_id', $subjectId)
                                    ),

                                    Chapter::class => $q->whereHas(
                                        'book.appGradeSubject',
                                        fn($qq) => $qq->where('subject_id', $subjectId)
                                    ),

                                    Section::class => $q->whereHas(
                                        'chapter.book.appGradeSubject',
                                        fn($qq) => $qq->where('subject_id', $subjectId)
                                    ),

                                };
                            }
                        );
                    }),

                Tables\Filters\SelectFilter::make('quizable_type')
                    ->label('سطح آزمون')
                    ->options([
                        Section::class => 'بخش',
                        Chapter::class => 'فصل / بعد از هر درس',
                        Book::class => 'کتاب / جامع / نوبت',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'draft' => 'پیش نویس',
                        'pending' => 'در انتظار بررسی',
                        'active' => 'فعال',
                        'inactive' => 'غیرفعال',
                    ]),

                Tables\Filters\TernaryFilter::make('is_free')
                    ->label('رایگان'),

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

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public static function getRelations(): array
    {
        return [
            QuestionsRelationManager::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    */

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Query Permission
    |--------------------------------------------------------------------------
    */

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with([
                // quizable چندریختی است (می‌تواند کتاب، فصل، یا بخش
                // باشد)؛ برای بارگذاری درست هرکدام باید زنجیره‌ی
                // رابطه‌ی مخصوص همان نوع را جدا مشخص کنیم، وگرنه
                // Eloquent نمی‌داند برای هر نوع کدام رابطه معتبر است.
                'quizable' => fn($morphTo) => $morphTo->morphWith([
                    Book::class => ['appGradeSubject.grade', 'appGradeSubject.subject'],
                    Chapter::class => ['book.appGradeSubject.grade', 'book.appGradeSubject.subject'],
                    Section::class => ['chapter.book.appGradeSubject.grade', 'chapter.book.appGradeSubject.subject'],
                ]),
            ]);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('SuperAdmin') || $user->hasRole('Admin')) {
            return $query;
        }

        if ($user->hasRole('Teacher')) {

            return $query->whereHas(
                'quizable',
                function ($builder) use ($user) {

                    $builder->whereHas(
                        'teacherAssignments',
                        function ($assignment) use ($user) {

                            $assignment
                                ->where('teacher_id', $user->id)
                                ->where('is_active', true);
                        }
                    );
                }
            );
        }

        return $query->whereRaw('1 = 0');
    }
}
