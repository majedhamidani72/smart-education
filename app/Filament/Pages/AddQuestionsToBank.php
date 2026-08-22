<?php

namespace App\Filament\Pages;

use App\Models\App;
use App\Models\AppGradeSubject;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\ContentItem;
use App\Models\Grade;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTopic;
use App\Models\Section;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

/**
 * افزودن سریع سوال به بانک
 * --------------------------------------------------------------------
 * قبلاً برای هر سوال باید کل فرم (از جمله انتخاب پایه/کتاب/فصل/
 * بخش) از نو پر می‌شد، حتی وقتی معلم داشت چندتا سوال پشت‌سرهم
 * برای همان بخش می‌نوشت. این صفحه دو فرم جدا دارد:
 *   ۱) «مسیر آموزشی» — فقط یک‌بار پر می‌شود و بین ذخیره‌ها دست‌
 *      نخورده می‌ماند.
 *   ۲) «متن سوال» — بعد از هر ذخیره خودکار خالی می‌شود تا سوال
 *      بعدی نوشته شود.
 * سوالات با وضعیت «پیش‌نویس» ذخیره می‌شوند و تا وقتی خودِ معلم
 * دکمه‌ی «ارسال برای بررسی» را نزند (چه یک سوال، چه صد سوال)،
 * پیش ادمین نمی‌روند — این ارسال از لیست اصلی «بانک سوالات»
 * (با همان قابلیت گروه‌بندی و اکشن دسته‌جمعی) انجام می‌شود.
 */
class AddQuestionsToBank extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?string $navigationGroup = 'آزمون آنلاین';

    protected static ?string $navigationLabel = 'افزودن سریع سوال';

    /**
     * این صفحه دیگر یک تب مستقل توی منو نیست — فقط از طریق دکمه‌ی
     * «ایجاد سوال» داخل «بانک سوالات» به آن می‌رسیم.
     */
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'افزودن سریع سوال به بانک';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.add-questions-to-bank';

    public ?array $context = [];

    public ?array $question = [];

    public int $savedCount = 0;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->hasRole('SuperAdmin') || $user?->hasRole('Admin') || $user?->hasRole('Teacher');
    }

    public function mount(): void
    {
        $initial = $this->resolveInitialContext();

        $this->contextForm->fill($initial);

        $this->questionForm->fill();
    }

    /**
     * اگر از دکمه‌ی «ادامه‌ی افزودن سوال» یک گروه خاص (توی بانک
     * سوالات) به این صفحه آمده باشیم، مسیر آموزشی همان گروه از
     * روی content_item_id که توی آدرس فرستاده شده، بازسازی و
     * از قبل پر می‌شود.
     */
    protected function resolveInitialContext(): array
    {
        $topicId = request()->query('question_topic_id');

        $contentItemId = request()->query('content_item_id');

        // حالت دوم: مستقیم از یک کتاب/فصل/بخش (مثلاً از دکمه‌ی
        // «ادامه‌ی نوشتن سوال» توی «تنظیمات آزمون») — بدون این‌که
        // یک محتوای مشخص در کار باشد.
        $bookId = request()->query('book_id');

        $chapterId = request()->query('chapter_id');

        $sectionId = request()->query('section_id');

        if (! $contentItemId && $bookId) {

            $book = Book::with('appGradeSubject')->find($bookId);

            if (! $book) {
                return $topicId ? ['question_topic_id' => $topicId] : [];
            }

            return [

                'app_id' => $book->appGradeSubject?->app_id,

                'grade_id' => $book->appGradeSubject?->grade_id,

                'subject_id' => $book->appGradeSubject?->subject_id,

                'book_id' => $book->id,

                'chapter_id' => $chapterId,

                'section_id' => $sectionId,

                'question_topic_id' => $topicId,

            ];
        }

        if (! $contentItemId) {
            return $topicId ? ['question_topic_id' => $topicId] : [];
        }

        $contentItem = ContentItem::query()
            ->with('chapter.book.appGradeSubject', 'section')
            ->find($contentItemId);

        $chapter = $contentItem?->chapter;

        if (! $chapter || ! $chapter->book) {
            return $topicId ? ['question_topic_id' => $topicId] : [];
        }

        $book = $chapter->book;

        return [

            'app_id' => $book->appGradeSubject?->app_id,

            'grade_id' => $book->appGradeSubject?->grade_id,

            'subject_id' => $book->appGradeSubject?->subject_id,

            'book_id' => $book->id,

            'chapter_id' => $chapter->id,

            'section_id' => $contentItem->section_id,

            'content_item_id' => $contentItem->id,

            'question_topic_id' => $topicId,

        ];
    }

    protected function getForms(): array
    {
        return [
            'contextForm',
            'questionForm',
        ];
    }

    /**
     * کتاب‌هایی که معلم فعلی به آن‌ها دسترسی دارد — همان منطق
     * QuizResource::teacherAssignedBooks. برای محدود کردن فیلدهای
     * اپلیکیشن/پایه/درس/کتاب فقط به دامنه‌ی واقعی معلم؛ ادمین/
     * سوپرادمین محدودیتی ندارند.
     */
    protected function teacherAssignedBooks(): \Illuminate\Support\Collection
    {
        $user = auth()->user();

        if (! $user->hasRole('Teacher') || $user->hasRole('Admin') || $user->hasRole('SuperAdmin')) {
            return collect();
        }

        $bookIds = \App\Models\TeacherAssignment::query()
            ->where('teacher_id', $user->id)
            ->where('is_active', true)
            ->pluck('book_id');

        return Book::query()
            ->whereIn('id', $bookIds)
            ->where('is_active', true)
            ->with('appGradeSubject')
            ->get();
    }

    public function contextForm(Form $form): Form
    {
        $teacherBooks = $this->teacherAssignedBooks();

        $isScoped = $teacherBooks->isNotEmpty();

        return $form
            ->schema([

                Forms\Components\Select::make('app_id')
                    ->label('اپلیکیشن')
                    ->options(function () use ($teacherBooks, $isScoped) {

                        if (! $isScoped) {
                            return App::where('is_active', true)->orderBy('sort_order')->pluck('title', 'id');
                        }

                        return App::whereIn('id', $teacherBooks->pluck('appGradeSubject.app_id')->unique())
                            ->orderBy('sort_order')->pluck('title', 'id');
                    })
                    ->searchable()->preload()->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('grade_id', null); $set('subject_id', null); $set('book_id', null);
                        $set('chapter_id', null); $set('section_id', null); $set('content_item_id', null);
                    })
                    ->required(),

                Forms\Components\Select::make('grade_id')
                    ->label('پایه')
                    ->options(function (Get $get) use ($teacherBooks, $isScoped) {
                        if (! $get('app_id')) return [];

                        if (! $isScoped) {
                            return Grade::whereHas('appGradeSubjects', fn($q) => $q->where('app_id', $get('app_id')))
                                ->orderBy('grade_number')->pluck('title', 'id');
                        }

                        // فقط پایه‌هایی که معلم واقعاً در همان
                        // اپلیکیشن کتاب دارد.
                        $gradeIds = $teacherBooks
                            ->filter(fn($book) => $book->appGradeSubject?->app_id == $get('app_id'))
                            ->pluck('appGradeSubject.grade_id')
                            ->unique();

                        return Grade::whereIn('id', $gradeIds)
                            ->orderBy('grade_number')->pluck('title', 'id');
                    })
                    ->searchable()->preload()->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('subject_id', null); $set('book_id', null);
                        $set('chapter_id', null); $set('section_id', null); $set('content_item_id', null);
                    })
                    ->required(),

                Forms\Components\Select::make('subject_id')
                    ->label('درس')
                    ->options(function (Get $get) use ($teacherBooks, $isScoped) {
                        if (! $get('grade_id')) return [];

                        if (! $isScoped) {
                            return Subject::whereHas('appGradeSubjects', fn($q) => $q
                                ->where('app_id', $get('app_id'))->where('grade_id', $get('grade_id')))
                                ->orderBy('sort_order')->pluck('title', 'id');
                        }

                        $subjectIds = $teacherBooks
                            ->filter(fn($book) => $book->appGradeSubject?->app_id == $get('app_id')
                                && $book->appGradeSubject?->grade_id == $get('grade_id'))
                            ->pluck('appGradeSubject.subject_id')
                            ->unique();

                        return Subject::whereIn('id', $subjectIds)
                            ->orderBy('sort_order')->pluck('title', 'id');
                    })
                    ->searchable()->preload()->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('book_id', null); $set('chapter_id', null);
                        $set('section_id', null); $set('content_item_id', null);
                    })
                    ->required(),

                Forms\Components\Select::make('book_id')
                    ->label('کتاب')
                    ->options(function (Get $get) use ($teacherBooks, $isScoped) {
                        if (! $get('subject_id')) return [];

                        if (! $isScoped) {
                            $ags = AppGradeSubject::where('app_id', $get('app_id'))
                                ->where('grade_id', $get('grade_id'))->where('subject_id', $get('subject_id'))->first();
                            if (! $ags) return [];
                            return Book::where('app_grade_subject_id', $ags->id)->where('is_active', true)
                                ->orderBy('sort_order')->pluck('title', 'id');
                        }

                        return $teacherBooks
                            ->filter(fn($book) => $book->appGradeSubject?->app_id == $get('app_id')
                                && $book->appGradeSubject?->grade_id == $get('grade_id')
                                && $book->appGradeSubject?->subject_id == $get('subject_id'))
                            ->pluck('title', 'id');
                    })
                    ->searchable()->preload()->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('chapter_id', null); $set('section_id', null); $set('content_item_id', null);
                    })
                    ->required(),

                Forms\Components\Select::make('chapter_id')
                    ->label('فصل (اختیاری — اگر سوال برای کل کتاب است، خالی بگذار)')
                    ->options(function (Get $get) {
                        if (! $get('book_id')) return [];
                        return Chapter::where('book_id', $get('book_id'))->where('is_active', true)
                            ->orderBy('sort_order')->pluck('title', 'id');
                    })
                    ->searchable()->preload()->live()
                    ->afterStateUpdated(fn(Set $set) => $set('section_id', null) ?: $set('content_item_id', null)),

                Forms\Components\Select::make('section_id')
                    ->label('بخش/درس (اختیاری)')
                    ->options(function (Get $get) {
                        if (! $get('chapter_id')) return [];
                        return Section::where('chapter_id', $get('chapter_id'))->where('is_active', true)
                            ->orderBy('sort_order')->pluck('title', 'id');
                    })
                    ->searchable()->preload()->live()
                    ->afterStateUpdated(fn(Set $set) => $set('content_item_id', null)),

            ])
            ->columns(4)
            ->statePath('context');
    }

    public function questionForm(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('difficulty')
                    ->label('سطح سختی')
                    ->options(['easy' => 'آسان', 'medium' => 'متوسط', 'hard' => 'سخت'])
                    ->default('medium')
                    ->required(),

                Forms\Components\Textarea::make('question_text')
                    ->label('متن سوال')
                    ->live()
                    ->required(fn(Get $get) => blank($get('image_path')))
                    ->helperText('حداقل یکی از متن سوال یا تصویر سوال باید پر شود.')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image_path')
                    ->label('تصویر سوال (اختیاری)')
                    ->disk('public')->directory('questions')->image()->openable()
                    ->live()
                    ->required(fn(Get $get) => blank($get('question_text')))
                    ->helperText('حداقل یکی از متن سوال یا تصویر سوال باید پر شود.')
                    ->columnSpanFull(),

                Forms\Components\Repeater::make('options')
                    ->label('گزینه‌ها')
                    ->schema([
                        Forms\Components\TextInput::make('option_text')->label('متن گزینه')->required()->columnSpan(2),

                        Forms\Components\FileUpload::make('image_path')
                            ->label('تصویر گزینه (اختیاری)')
                            ->disk('public')
                            ->directory('question-options')
                            ->image()
                            ->openable(),

                        Forms\Components\Toggle::make('is_correct')->label('پاسخ صحیح')->default(false),
                    ])
                    ->columns(4)
                    ->defaultItems(4)
                    ->minItems(2)
                    ->maxItems(6)
                    ->reorderable(false)
                    ->addActionLabel('افزودن گزینه')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('explanation')
                    ->label('توضیح پاسخ')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('explanation_image_path')
                    ->label('تصویر توضیح پاسخ (اختیاری)')
                    ->disk('public')
                    ->directory('question-explanations')
                    ->image()
                    ->openable()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('recommendation_text')
                    ->label('پیشنهاد مطالعه در صورت جواب اشتباه (اختیاری)')
                    ->placeholder('مثلاً: صفحه ۴۵ کتاب را دوباره بخوان، یا کلیپ فصل ۳ بخش ۲ را ببین')
                    ->helperText('برخلاف «توضیح پاسخ» بالا، این متن فقط در گزارش پایان آزمون (وقتی دانش‌آموز غلط جواب داده) نمایش داده می‌شود.')
                    ->rows(2)
                    ->columnSpanFull(),

            ])
            ->columns(2)
            ->statePath('question');
    }

    /**
     * ذخیره‌ی سوال فعلی — مشترک بین دو دکمه.
     */
    protected function persistQuestion(): bool
    {
        $context = $this->contextForm->getState();

        $questionData = $this->questionForm->getState();

        // چون دیگر فیلد «محتوای آموزشی» به‌صورت دستی توی فرم
        // نیست، اینجا خودکار و بی‌صدا از روی بخش/فصلی که انتخاب
        // شده، اولین محتوای موجود همان‌جا پیدا و وصل می‌شود — تا
        // این سوال هم توی «بانک سوالات» سه‌سطحی درست دیده شود.
        if (empty($context['content_item_id'])) {

            $contentItemQuery = ContentItem::query();

            if (! empty($context['section_id'])) {

                $contentItemQuery->where('section_id', $context['section_id']);

            } elseif (! empty($context['chapter_id'])) {

                $contentItemQuery->where('chapter_id', $context['chapter_id']);

            } else {

                $contentItemQuery->whereRaw('1 = 0');
            }

            $context['content_item_id'] = $contentItemQuery
                ->orderBy('sort_order')
                ->value('id');
        }

        // بدون حداقل یک گزینه‌ی «پاسخ صحیح»، سوال اصلاً ذخیره
        // نمی‌شود.
        $hasCorrect = collect($questionData['options'] ?? [])->contains(
            fn($option) => ($option['is_correct'] ?? false) === true
        );

        if (! $hasCorrect) {

            Notification::make()
                ->title('حداقل یکی از گزینه‌ها باید «پاسخ صحیح» باشد.')
                ->danger()
                ->send();

            return false;
        }

        DB::transaction(function () use ($context, $questionData) {

            $question = Question::create([

                'content_item_id' => $context['content_item_id'] ?? null,

                'book_id' => $context['book_id'] ?? null,

                'chapter_id' => $context['chapter_id'] ?? null,

                'section_id' => $context['section_id'] ?? null,

                'question_topic_id' => $context['question_topic_id'] ?? null,

                'question_text' => $questionData['question_text'] ?? null,

                'image_path' => is_array($questionData['image_path'] ?? null)
                    ? collect($questionData['image_path'])->first()
                    : ($questionData['image_path'] ?? null),

                'difficulty' => $questionData['difficulty'],

                'explanation' => $questionData['explanation'],

                'explanation_image_path' => is_array($questionData['explanation_image_path'] ?? null)
                    ? collect($questionData['explanation_image_path'])->first()
                    : ($questionData['explanation_image_path'] ?? null),

                'recommendation_text' => $questionData['recommendation_text'] ?? null,

                'created_by' => auth()->id(),

                // تا خودِ معلم دکمه‌ی «ارسال برای بررسی» را نزند،
                // این سوال اصلاً وارد صف بررسی ادمین نمی‌شود.
                'status' => 'draft',

                'is_active' => true,

            ]);

            foreach ($questionData['options'] as $option) {

                QuestionOption::create([

                    'question_id' => $question->id,

                    'option_text' => $option['option_text'],

                    'image_path' => is_array($option['image_path'] ?? null)
                        ? collect($option['image_path'])->first()
                        : ($option['image_path'] ?? null),

                    'is_correct' => $option['is_correct'] ?? false,

                ]);
            }
        });

        $this->savedCount++;

        // فقط فرم سوال خالی می‌شود؛ مسیر آموزشی دست‌نخورده می‌ماند.
        $this->questionForm->fill();

        return true;
    }

    public function saveAndContinue(): void
    {
        if (! $this->persistQuestion()) {
            return;
        }

        Notification::make()
            ->title('سوال ذخیره شد. می‌توانی سوال بعدی را بنویسی.')
            ->success()
            ->send();
    }

    public function saveAndExit(): void
    {
        if (! $this->persistQuestion()) {
            return;
        }

        Notification::make()
            ->title($this->savedCount.' سوال به‌صورت پیش‌نویس ذخیره شد.')
            ->success()
            ->send();

        // به‌جای برگشتن به اولین صفحه‌ی «بانک سوالات» (لیست کتاب‌ها)،
        // مستقیم به همان بخش/فصل/کل‌کتابی که همین الان رویش کار
        // می‌کردیم برمی‌گردیم — اطلاعات مسیر از طریق آدرس منتقل
        // می‌شود.
        $context = $this->contextForm->getState();

        $this->redirect(\App\Filament\Resources\QuestionResource::getUrl('index', [
            'book_id' => $context['book_id'] ?? null,
            'chapter_id' => $context['chapter_id'] ?? null,
            'section_id' => $context['section_id'] ?? null,
        ]));
    }
}
