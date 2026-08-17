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
use App\Models\TeacherAssignment;
use App\Traits\FiltersByTeacherAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
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

    /**
     * کتاب‌هایی که معلم فعلی به آن‌ها دسترسی دارد.
     *
     * این لیست از روی TeacherAssignmentهای فعالِ معلم خوانده
     * می‌شود (چیزی که سوپرادمین/ادمین در «مدیریت معلمان» برای
     * او مشخص کرده). خروجی، دامنه‌ی مجاز فیلدهای اپلیکیشن،
     * پایه، درس و کتاب را در فرم ایجاد محتوا محدود می‌کند —
     * معلم فقط اجازه‌ی «انتخاب» از این دامنه را دارد،
     * نه «ایجاد» گزینه‌ی جدید.
     */
    protected static function teacherAssignedBooks(): \Illuminate\Support\Collection
    {
        $bookIds = TeacherAssignment::query()
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

        // دامنه‌ی مجاز معلم؛ برای ادمین/سوپرادمین همیشه خالی
        // می‌ماند چون آن‌ها به کل ساختار آموزشی دسترسی دارند.
        $teacherBooks = $isTeacher
            ? static::teacherAssignedBooks()
            : collect();

        return $form->schema([

            Forms\Components\Section::make('اطلاعات آموزشی')

                ->columns(2)

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
                        // چون اپلیکیشنِ تازه‌ساخته‌شده ممکن است در
                        // لحظه‌ی نمایش هنوز داخل لیست options() بالا
                        // نباشد (مثلاً فیلترهای معلم)، این متد تضمین
                        // می‌کند که همیشه عنوان واقعی نمایش داده شود،
                        // نه شناسه‌ی عددی (id) به‌عنوان جایگزین.
                        ->getOptionLabelUsing(fn($value) => App::find($value)?->title)
                        ->live()
                        ->dehydrated(false)
                        ->required()
                        ->default(function () use ($isTeacher, $teacherBooks) {

                            // اگر معلم فقط یک اپلیکیشن در اختیار دارد،
                            // به‌صورت خودکار همان انتخاب می‌شود.
                            if (! $isTeacher) {
                                return null;
                            }

                            $appIds = $teacherBooks
                                ->pluck('appGradeSubject.app_id')
                                ->unique();

                            return $appIds->count() === 1
                                ? $appIds->first()
                                : null;
                        })
                        ->when(
                            ! $isTeacher,
                            fn(Forms\Components\Select $field) => $field
                                ->createOptionForm([

                                    Forms\Components\TextInput::make('title')
                                        ->label('عنوان اپلیکیشن')
                                        ->required(),

                                    Forms\Components\Toggle::make('is_active')
                                        ->label('فعال')
                                        ->default(true),

                                ])
                                ->createOptionUsing(function (array $data) {

                                    // اگر اپلیکیشنی با همین عنوان (و در
                                    // نتیجه همین slug) از قبل ساخته
                                    // شده باشد، به‌جای خطای یکتایی یا
                                    // ساخت رکورد تکراری، به کاربر
                                    // اطلاع می‌دهیم که از قبل وجود
                                    // دارد و همان انتخاب می‌شود.
                                    $slug = Str::slug($data['title']);

                                    $existing = App::where('slug', $slug)->first();

                                    if ($existing) {

                                        Notification::make()
                                            ->title('این اپلیکیشن از قبل وجود دارد و انتخاب شد.')
                                            ->warning()
                                            ->send();

                                        return $existing->id;
                                    }

                                    $app = App::create([
                                        'title' => $data['title'],
                                        'slug' => $slug,
                                        'is_active' => $data['is_active'],
                                        'sort_order' => 1,
                                    ]);

                                    return $app->id;
                                })
                        )
                        ->afterStateUpdated(function (Set $set) {

                            $set('grade_id', null);
                            $set('subject_id', null);
                            $set('book_id', null);
                            $set('chapter_id', null);
                            $set('section_id', null);
                        }),

                    Forms\Components\Select::make('grade_id')
                        ->label('پایه')
                        ->options(function (Get $get) use ($isTeacher, $teacherBooks) {

                            if (! $get('app_id')) {
                                return [];
                            }

                            if ($isTeacher) {
                                return $teacherBooks
                                    ->filter(
                                        fn($book) =>
                                        $book->appGradeSubject?->app_id == $get('app_id')
                                    )
                                    ->pluck('appGradeSubject.grade_id')
                                    ->unique()
                                    ->mapWithKeys(fn($gradeId) => [
                                        $gradeId => Grade::find($gradeId)?->title,
                                    ]);
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
                        ->getOptionLabelUsing(fn($value) => Grade::find($value)?->title)
                        ->live()
                        ->dehydrated(false)
                        ->required()
                        ->default(function () use ($isTeacher, $teacherBooks) {

                            if (! $isTeacher) {
                                return null;
                            }

                            $gradeIds = $teacherBooks
                                ->pluck('appGradeSubject.grade_id')
                                ->unique();

                            return $gradeIds->count() === 1
                                ? $gradeIds->first()
                                : null;
                        })
                        ->when(
                            ! $isTeacher,
                            fn(Forms\Components\Select $field) => $field
                                ->createOptionForm([

                                    Forms\Components\TextInput::make('title')
                                        ->label('عنوان پایه')
                                        ->required(),

                                    Forms\Components\TextInput::make('grade_number')
                                        ->label('شماره پایه')
                                        ->numeric()
                                        ->required(),

                                ])
                                ->createOptionUsing(function (array $data) {

                                    // پایه ممکن است قبلاً برای اپلیکیشن
                                    // دیگری ساخته شده باشد. شماره‌ی
                                    // پایه (grade_number) و slug هر دو
                                    // در دیتابیس یکتا هستند، پس هر دو
                                    // را بررسی می‌کنیم.
                                    $slug = Str::slug($data['title']);

                                    $existing = Grade::where('grade_number', $data['grade_number'])
                                        ->orWhere('slug', $slug)
                                        ->first();

                                    if ($existing) {

                                        Notification::make()
                                            ->title('این پایه از قبل وجود دارد و انتخاب شد.')
                                            ->warning()
                                            ->send();

                                        return $existing->id;
                                    }

                                    $grade = Grade::create([
                                        'title' => $data['title'],
                                        'slug' => $slug,
                                        'grade_number' => $data['grade_number'],
                                        'sort_order' => $data['grade_number'],
                                        'is_active' => true,
                                    ]);

                                    // توجه: این پایه هنوز به هیچ اپلیکیشنی
                                    // متصل نیست. اتصال (app_grade_subjects)
                                    // در مرحله‌ی انتخاب/ایجاد «درس» انجام
                                    // می‌شود، چون پیوند سه‌طرفه‌ی
                                    // اپلیکیشن+پایه+درس در همان لحظه کامل
                                    // می‌شود.
                                    return $grade->id;
                                })
                        )
                        ->afterStateUpdated(function (Set $set) {

                            $set('subject_id', null);
                            $set('book_id', null);
                            $set('chapter_id', null);
                            $set('section_id', null);
                        }),

                    Forms\Components\Select::make('subject_id')
                        ->label('درس')
                        ->options(function (Get $get) use ($isTeacher, $teacherBooks) {

                            if (! $get('grade_id')) {
                                return [];
                            }

                            if ($isTeacher) {
                                return $teacherBooks
                                    ->filter(
                                        fn($book) =>
                                        $book->appGradeSubject?->app_id == $get('app_id')
                                        && $book->appGradeSubject?->grade_id == $get('grade_id')
                                    )
                                    ->pluck('appGradeSubject.subject_id')
                                    ->unique()
                                    ->mapWithKeys(fn($subjectId) => [
                                        $subjectId => Subject::find($subjectId)?->title,
                                    ]);
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
                        ->getOptionLabelUsing(fn($value) => Subject::find($value)?->title)
                        ->live()
                        ->dehydrated(false)
                        ->required()
                        ->default(function () use ($isTeacher, $teacherBooks) {

                            if (! $isTeacher) {
                                return null;
                            }

                            $subjectIds = $teacherBooks
                                ->pluck('appGradeSubject.subject_id')
                                ->unique();

                            return $subjectIds->count() === 1
                                ? $subjectIds->first()
                                : null;
                        })
                        ->when(
                            ! $isTeacher,
                            fn(Forms\Components\Select $field) => $field
                                ->createOptionForm([

                                    Forms\Components\TextInput::make('title')
                                        ->label('عنوان درس')
                                        ->required(),

                                ])
                                ->createOptionUsing(function (array $data, Get $get) {

                                    // درس ممکن است بین چند اپلیکیشن/پایه
                                    // مشترک باشد؛ بر اساس slug بررسی
                                    // می‌کنیم که آیا از قبل وجود دارد.
                                    $slug = Str::slug($data['title']);

                                    $existingSubject = Subject::where('slug', $slug)->first();

                                    if ($existingSubject) {

                                        Notification::make()
                                            ->title('این درس از قبل وجود دارد و انتخاب شد.')
                                            ->warning()
                                            ->send();
                                    }

                                    $subject = $existingSubject ?? Subject::create([
                                        'title' => $data['title'],
                                        'slug' => $slug,
                                        'sort_order' => 1,
                                        'is_active' => true,
                                    ]);

                                    // این همان لحظه‌ای‌ست که پیوند
                                    // سه‌طرفه‌ی اپلیکیشن+پایه+درس کامل
                                    // می‌شود و «درس» زیر همین اپلیکیشن
                                    // و پایه قابل مشاهده خواهد شد.
                                    AppGradeSubject::firstOrCreate([

                                        'app_id' => $get('app_id'),

                                        'grade_id' => $get('grade_id'),

                                        'subject_id' => $subject->id,

                                    ]);

                                    return $subject->id;
                                })
                        )
                        ->afterStateUpdated(function (Set $set) {

                            $set('book_id', null);
                            $set('chapter_id', null);
                            $set('section_id', null);
                        }),

                    Forms\Components\Select::make('book_id')
                        ->label('کتاب')
                        ->options(function (Get $get) use ($isTeacher, $teacherBooks) {

                            if (! $get('subject_id')) {
                                return [];
                            }

                            if ($isTeacher) {
                                return $teacherBooks
                                    ->filter(
                                        fn($book) =>
                                        $book->appGradeSubject?->app_id == $get('app_id')
                                        && $book->appGradeSubject?->grade_id == $get('grade_id')
                                        && $book->appGradeSubject?->subject_id == $get('subject_id')
                                    )
                                    ->pluck('title', 'id');
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
                        ->getOptionLabelUsing(fn($value) => Book::find($value)?->title)
                        ->live()
                        ->dehydrated(false)
                        ->required()
                        ->default(function () use ($isTeacher, $teacherBooks) {

                            return $isTeacher && $teacherBooks->count() === 1
                                ? $teacherBooks->first()->id
                                : null;
                        })
                        ->when(
                            ! $isTeacher,
                            fn(Forms\Components\Select $field) => $field
                                ->createOptionForm([

                                    Forms\Components\TextInput::make('title')
                                        ->label('عنوان کتاب')
                                        ->required(),

                                    Forms\Components\TextInput::make('sort_order')
                                        ->numeric()
                                        ->default(1),

                                    Forms\Components\Toggle::make('is_active')
                                        ->default(true),

                                ])
                                ->createOptionUsing(function (array $data, Get $get) {

                                    $appGradeSubject = AppGradeSubject::query()
                                        ->where('app_id', $get('app_id'))
                                        ->where('grade_id', $get('grade_id'))
                                        ->where('subject_id', $get('subject_id'))
                                        ->first();

                                    // یکتایی کتاب بر اساس ترکیب
                                    // app_grade_subject_id + slug است.
                                    $slug = Str::slug($data['title']);

                                    $existing = Book::where('app_grade_subject_id', $appGradeSubject->id)
                                        ->where('slug', $slug)
                                        ->first();

                                    if ($existing) {

                                        Notification::make()
                                            ->title('این کتاب از قبل وجود دارد و انتخاب شد.')
                                            ->warning()
                                            ->send();

                                        return $existing->id;
                                    }

                                    $book = Book::create([
                                        'app_grade_subject_id' => $appGradeSubject->id,
                                        'title' => $data['title'],
                                        'slug' => $slug,
                                        'sort_order' => $data['sort_order'],
                                        'is_active' => $data['is_active'],
                                    ]);

                                    return $book->id;
                                })
                        )
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

                            // فصل و بخش برای معلم هم انتخاب‌شدنی و
                            // هم قابل‌ایجادند (بر خلاف اپلیکیشن/پایه/
                            // درس/کتاب که فقط سوپرادمین/ادمین می‌سازند)
                            // چون معلم باید بتواند داخل همان کتابِ
                            // اختصاص‌داده‌شده، فصل و بخش تازه اضافه کند.

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

                            $slug = Str::slug($data['title']);

                            $existing = Chapter::where('book_id', $get('book_id'))
                                ->where('slug', $slug)
                                ->first();

                            if ($existing) {

                                Notification::make()
                                    ->title('این فصل از قبل وجود دارد و انتخاب شد.')
                                    ->warning()
                                    ->send();

                                return $existing->id;
                            }

                            $chapter = Chapter::create([

                                'book_id' => $get('book_id'),

                                'title' => $data['title'],

                                'slug' => $slug,

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

                            $slug = Str::slug($data['title']);

                            $existing = Section::where('chapter_id', $get('chapter_id'))
                                ->where('slug', $slug)
                                ->first();

                            if ($existing) {

                                Notification::make()
                                    ->title('این بخش از قبل وجود دارد و انتخاب شد.')
                                    ->warning()
                                    ->send();

                                return $existing->id;
                            }

                            $section = Section::create([

                                'chapter_id' => $get('chapter_id'),

                                'title' => $data['title'],

                                'slug' => $slug,

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
                                ->value('slug') === 'teaching';
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
                                ->value('slug') === 'teaching';
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
                                ->value('slug') === 'sample_questions';
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
                                ->value('slug') === 'sample_questions';
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
