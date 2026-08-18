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
use App\Support\Jalali;
use App\Traits\FiltersByTeacherAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
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

                            // برای معلم، لیست پایه‌ها از دامنه‌ی
                            // خودِ او ساخته می‌شود و منتظر انتخاب
                            // اپلیکیشن نمی‌ماند — این‌طوری وقتی
                            // فیلد app_id با مقدار پیش‌فرض پر
                            // می‌شود، گزینه‌های پایه هم از همون
                            // ابتدا آماده‌اند و مقدار پیش‌فرضِ
                            // grade_id هم به‌درستی نمایش داده
                            // می‌شود (نه خالی).
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

                    // توجه: دیگر اینجا یک فیلد «عنوان» قابل‌مشاهده
                    // نیست — چون همین عنوان توی فیلدهای اختصاصی هر
                    // نوع محتوا (عنوان ویدئو / عنوان صفحه / عنوان
                    // PDF) هم گرفته می‌شد و برای کاربر گیج‌کننده
                    // بود. عنوان نهایی، هنگام ذخیره‌سازی (نه اینجا
                    // در فرم)، از روی همان فیلد اختصاصی ساخته می‌شود
                    // — نگاه کنید به CreateContentItem::resolveTitle
                    // و EditContentItem::resolveTitle.

                    // «required» عمداً اینجا گذاشته نمی‌شود: این
                    // فیلد مخفی فقط موقع ذخیره‌سازی (در سرور، داخل
                    // resolveTitle) پر می‌شود، نه قبل از آن. اگر
                    // required باشد، چون فرم قبل از رسیدن به آن
                    // کد سرور اعتبارسنجی می‌شود، همیشه (به‌صورت
                    // نامرئی و بدون هیچ پیام خطایی) رد می‌شود.
                    // اجباری بودن «عنوان» به‌جای این، روی خودِ
                    // فیلدهای اختصاصی هر نوع محتوا (عنوان ویدئو /
                    // عنوان صفحه / عنوان PDF) گذاشته شده.
                    Forms\Components\Hidden::make('title'),

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
                        ->required(function (Get $get) {

                            return ContentType::query()
                                ->whereKey($get('content_type_id'))
                                ->value('slug') === 'teaching';
                        })
                        ->visible(function (Get $get) {

                            return ContentType::query()
                                ->whereKey($get('content_type_id'))
                                ->value('slug') === 'teaching';
                        }),

                    Forms\Components\FileUpload::make('video.video_file')
                        ->label('فایل ویدئو')
                        ->disk('public')
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
                                ->disk('public')
                                ->directory('step-by-step')
                                ->image()
                                ->imagePreviewHeight('180')
                                ->panelLayout('integrated')
                                ->openable()
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
                        ->required(function (Get $get) {

                            return ContentType::query()
                                ->whereKey($get('content_type_id'))
                                ->value('slug') === 'sample_questions';
                        })
                        ->visible(function (Get $get) {

                            return ContentType::query()
                                ->whereKey($get('content_type_id'))
                                ->value('slug') === 'sample_questions';
                        }),

                    Forms\Components\FileUpload::make('pdfFile.file')

                        ->label('فایل PDF')

                        ->disk('public')

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

    /**
     * نمایش کامل اطلاعات یک محتوای آموزشی (دکمه‌ی «نمایش»).
     * --------------------------------------------------------------------
     * قبلاً این دکمه چیزی برای نمایش نداشت و به‌جای آن، فرم
     * پیچیده‌ی «ایجاد/ویرایش» به‌صورت غیرفعال باز می‌شد که چون
     * فیلدهای کمکی (اپلیکیشن/پایه/درس/کتاب/فصل) مقداردهی اولیه
     * نمی‌شدند، عملاً خالی به نظر می‌رسید. این infolist مستقل و
     * فقط-خواندنی است و مستقیماً از روی رابطه‌های واقعی رکورد
     * (section.chapter.book...) اطلاعات را می‌خواند، نه از فیلدهای
     * کمکیِ فرم.
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            InfolistSection::make('مسیر آموزشی')

                ->columns(3)

                ->schema([

                    TextEntry::make('section.chapter.book.appGradeSubject.app.title')
                        ->label('اپلیکیشن')
                        ->placeholder('—'),

                    TextEntry::make('section.chapter.book.appGradeSubject.grade.title')
                        ->label('پایه')
                        ->placeholder('—'),

                    TextEntry::make('section.chapter.book.appGradeSubject.subject.title')
                        ->label('درس')
                        ->placeholder('—'),

                    TextEntry::make('section.chapter.book.title')
                        ->label('کتاب')
                        ->placeholder('—'),

                    TextEntry::make('section.chapter.title')
                        ->label('فصل')
                        ->placeholder('—'),

                    TextEntry::make('section.title')
                        ->label('بخش')
                        ->placeholder('بدون بخش'),

                ]),

            InfolistSection::make('اطلاعات محتوا')

                ->columns(2)

                ->schema([

                    TextEntry::make('contentType.title')
                        ->label('نوع محتوا')
                        ->badge(),

                    TextEntry::make('title')
                        ->label('عنوان'),

                    TextEntry::make('page_number')
                        ->label('شماره صفحه')
                        ->placeholder('—'),

                    TextEntry::make('is_free')
                        ->label('نوع دسترسی')
                        ->formatStateUsing(fn($state) => $state ? 'رایگان' : 'پولی')
                        ->badge()
                        ->color(fn($state) => $state ? 'success' : 'gray'),

                    TextEntry::make('description')
                        ->label('توضیحات')
                        ->placeholder('—')
                        ->columnSpanFull(),

                ]),

            InfolistSection::make('فایل ویدئو')

                ->visible(fn($record) => $record->contentType?->slug === 'teaching')

                ->schema([

                    // پخش‌کننده‌ی واقعی — برای بررسی محتوا نیازی
                    // به دانلود کردن فایل نیست.
                    ViewEntry::make('video.video_url')
                        ->label('پیش‌نمایش')
                        ->view('filament.infolists.video-player')
                        ->columnSpanFull(),

                    InfolistGrid::make(3)->schema([

                        TextEntry::make('video.video_url')
                            ->label('لینک مستقیم فایل')
                            ->placeholder('—')
                            ->copyable()
                            ->url(fn($record) => $record->video?->video_url, shouldOpenInNewTab: true),

                        TextEntry::make('video.file_size_readable')
                            ->label('حجم فایل')
                            ->placeholder('—'),

                        TextEntry::make('video.mime_type')
                            ->label('نوع فایل')
                            ->placeholder('—'),

                    ]),

                ]),

            InfolistSection::make('صفحات گام‌به‌گام')

                ->visible(fn($record) => $record->contentType?->slug === 'step_by_step')

                ->schema([

                    RepeatableEntry::make('stepByStep.pages')

                        ->label('')

                        ->schema([

                            InfolistGrid::make(4)->schema([

                                TextEntry::make('title')
                                    ->label('عنوان صفحه')
                                    ->placeholder('—'),

                                TextEntry::make('sort_order')
                                    ->label('ترتیب'),

                                ViewEntry::make('image')
                                    ->label('تصویر')
                                    ->view('filament.infolists.step-image'),

                                TextEntry::make('image')
                                    ->label('حجم فایل')
                                    ->formatStateUsing(function ($state) {

                                        if (blank($state) || ! Storage::disk('public')->exists($state)) {
                                            return '—';
                                        }

                                        $bytes = Storage::disk('public')->size($state);

                                        return $bytes > 1024 * 1024
                                            ? number_format($bytes / 1024 / 1024, 2).' MB'
                                            : number_format($bytes / 1024, 1).' KB';
                                    }),

                            ]),

                        ]),

                ]),

            InfolistSection::make('فایل نمونه سوالات')

                ->visible(fn($record) => $record->contentType?->slug === 'sample_questions')

                ->schema([

                    ViewEntry::make('pdfFile.file_url')
                        ->label('پیش‌نمایش')
                        ->view('filament.infolists.pdf-preview')
                        ->columnSpanFull(),

                    InfolistGrid::make(2)->schema([

                        TextEntry::make('pdfFile.file_url')
                            ->label('لینک مستقیم فایل PDF')
                            ->placeholder('—')
                            ->copyable()
                            ->url(fn($record) => $record->pdfFile?->file_url, shouldOpenInNewTab: true),

                        TextEntry::make('pdfFile.file_size_readable')
                            ->label('حجم فایل')
                            ->placeholder('—'),

                    ]),

                ]),

            InfolistSection::make('وضعیت بررسی')

                ->columns(2)

                ->schema([

                    TextEntry::make('status')
                        ->label('وضعیت')
                        ->badge()
                        ->formatStateUsing(fn($state) => match ($state) {
                            'pending' => 'در انتظار بررسی',
                            'approved' => 'تأیید شده',
                            'rejected' => 'رد شده',
                            'published' => 'منتشر شده',
                            default => $state,
                        })
                        ->color(fn($state) => match ($state) {
                            'approved', 'published' => 'success',
                            'rejected' => 'danger',
                            default => 'warning',
                        }),

                    TextEntry::make('rejection_reason')
                        ->label('دلیل رد')
                        ->placeholder('—')
                        ->color('danger')
                        ->visible(fn($record) => $record->status === 'rejected'),

                    TextEntry::make('creator.name')
                        ->label('ایجادکننده')
                        ->placeholder('—'),

                    TextEntry::make('reviewer.name')
                        ->label('بررسی‌کننده')
                        ->placeholder('—'),

                    TextEntry::make('created_at')
                        ->label('تاریخ ثبت')
                        ->formatStateUsing(fn($state) => Jalali::format($state)),

                    TextEntry::make('reviewed_at')
                        ->label('تاریخ بررسی')
                        ->formatStateUsing(fn($state) => Jalali::format($state))
                        ->placeholder('—'),

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

                // بسته به نوع محتوا، حجم از جدول مرتبط (ویدئو یا
                // PDF) خوانده می‌شود. برای گام‌به‌گام، چون چند
                // تصویر جداگانه دارد، مجموع حجم همه‌ی صفحات نمایش
                // داده می‌شود.
                Tables\Columns\TextColumn::make('file_size')
                    ->label('حجم فایل')
                    ->state(function ($record) {

                        return match ($record->contentType?->slug) {

                            'teaching' => $record->video?->file_size_readable,

                            'sample_questions' => $record->pdfFile?->file_size_readable,

                            'step_by_step' => $record->stepByStep
                                ?->pages
                                ->sum(
                                    fn($page) =>
                                    Storage::disk('public')->exists($page->image ?? '')
                                        ? Storage::disk('public')->size($page->image)
                                        : 0
                                ),

                            default => null,
                        };
                    })
                    ->formatStateUsing(function ($state, $record) {

                        // برای گام‌به‌گام، state یک عدد خام بایت
                        // است و باید خوانا شود؛ برای بقیه، از قبل
                        // خوانا برگشته (fileSizeReadable).
                        if ($record->contentType?->slug === 'step_by_step' && is_numeric($state)) {

                            return $state > 0
                                ? number_format($state / 1024 / 1024, 2).' MB'
                                : null;
                        }

                        return $state;
                    })
                    ->placeholder('—'),

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
                    // فقط ادمین/سوپرادمین با کلیک روی خودِ وضعیت،
                    // یک پنجره‌ی سریع برای تغییرش باز می‌کند —
                    // نیازی به رفتن داخل فرم کامل ویرایش نیست.
                    // معلم برای این ستون فقط نمایش‌گر می‌بیند،
                    // چون کلیک‌پذیر بودنش هم مشروط شده.
                    ->action(
                        Tables\Actions\Action::make('changeStatus')
                            ->label('تغییر وضعیت')
                            ->visible(
                                fn() =>
                                auth()->user()?->hasRole('Admin')
                                || auth()->user()?->hasRole('SuperAdmin')
                            )
                            ->form([

                                Forms\Components\Select::make('status')
                                    ->label('وضعیت جدید')
                                    ->options([
                                        'pending' => 'در انتظار بررسی',
                                        'approved' => 'تأیید شده',
                                        'rejected' => 'رد شده',
                                        'published' => 'منتشر شده',
                                    ])
                                    ->required()
                                    ->live(),

                                Forms\Components\Textarea::make('rejection_reason')
                                    ->label('دلیل رد')
                                    ->required(fn(Get $get) => $get('status') === 'rejected')
                                    ->visible(fn(Get $get) => $get('status') === 'rejected'),

                            ])
                            ->fillForm(fn($record) => [
                                'status' => $record->status,
                                'rejection_reason' => $record->rejection_reason,
                            ])
                            ->action(function (array $data, $record): void {

                                $record->update([

                                    'status' => $data['status'],

                                    'rejection_reason' => $data['status'] === 'rejected'
                                        ? $data['rejection_reason']
                                        : null,

                                    'reviewed_by' => auth()->id(),

                                    'reviewed_at' => now(),

                                ]);

                                Notification::make()
                                    ->title('وضعیت محتوا به‌روزرسانی شد.')
                                    ->success()
                                    ->send();
                            })
                    ),

                // دلیل رد فقط وقتی وضعیت «رد شده» باشد مقدار
                // دارد؛ در غیر این صورت خط تیره نمایش داده می‌شود.
                // معلم با دیدن همین ستون می‌فهمد چرا محتوایش رد
                // شده، بدون نیاز به باز کردن فرم ویرایش.
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('دلیل رد')
                    ->placeholder('—')
                    ->limit(60)
                    ->wrap()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('ایجادکننده')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('بررسی‌کننده')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => ! $isTeacher),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn($state) => Jalali::format($state))
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

                    ]),

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

                'video',

                'pdfFile',

                'stepByStep.pages',

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
