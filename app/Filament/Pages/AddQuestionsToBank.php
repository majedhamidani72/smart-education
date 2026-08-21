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
        $this->contextForm->fill();

        $this->questionForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'contextForm',
            'questionForm',
        ];
    }

    public function contextForm(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('app_id')
                    ->label('اپلیکیشن')
                    ->options(App::where('is_active', true)->orderBy('sort_order')->pluck('title', 'id'))
                    ->searchable()->preload()->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('grade_id', null); $set('subject_id', null); $set('book_id', null);
                        $set('chapter_id', null); $set('section_id', null); $set('content_item_id', null);
                    })
                    ->required(),

                Forms\Components\Select::make('grade_id')
                    ->label('پایه')
                    ->options(function (Get $get) {
                        if (! $get('app_id')) return [];
                        return Grade::whereHas('appGradeSubjects', fn($q) => $q->where('app_id', $get('app_id')))
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
                    ->options(function (Get $get) {
                        if (! $get('grade_id')) return [];
                        return Subject::whereHas('appGradeSubjects', fn($q) => $q
                            ->where('app_id', $get('app_id'))->where('grade_id', $get('grade_id')))
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
                    ->options(function (Get $get) {
                        if (! $get('subject_id')) return [];
                        $ags = AppGradeSubject::where('app_id', $get('app_id'))
                            ->where('grade_id', $get('grade_id'))->where('subject_id', $get('subject_id'))->first();
                        if (! $ags) return [];
                        return Book::where('app_grade_subject_id', $ags->id)->where('is_active', true)
                            ->orderBy('sort_order')->pluck('title', 'id');
                    })
                    ->searchable()->preload()->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('chapter_id', null); $set('section_id', null); $set('content_item_id', null);
                    })
                    ->required(),

                Forms\Components\Select::make('chapter_id')
                    ->label('فصل')
                    ->options(function (Get $get) {
                        if (! $get('book_id')) return [];
                        return Chapter::where('book_id', $get('book_id'))->where('is_active', true)
                            ->orderBy('sort_order')->pluck('title', 'id');
                    })
                    ->searchable()->preload()->live()
                    ->afterStateUpdated(fn(Set $set) => $set('section_id', null) ?: $set('content_item_id', null))
                    ->required(),

                Forms\Components\Select::make('section_id')
                    ->label('بخش (اختیاری)')
                    ->options(function (Get $get) {
                        if (! $get('chapter_id')) return [];
                        return Section::where('chapter_id', $get('chapter_id'))->where('is_active', true)
                            ->orderBy('sort_order')->pluck('title', 'id');
                    })
                    ->searchable()->preload()->live()
                    ->afterStateUpdated(fn(Set $set) => $set('content_item_id', null)),

                Forms\Components\Select::make('content_item_id')
                    ->label('محتوای آموزشی (کلیپ، اختیاری)')
                    ->options(function (Get $get) {
                        if (! $get('section_id')) return [];
                        return ContentItem::where('section_id', $get('section_id'))->pluck('title', 'id');
                    })
                    ->searchable()->preload(),

                Forms\Components\Select::make('question_topic_id')
                    ->label('موضوع سوال')
                    ->options(QuestionTopic::pluck('title', 'id'))
                    ->searchable()->preload()->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('title')->label('عنوان موضوع')->required(),
                    ])
                    ->createOptionUsing(fn(array $data) => QuestionTopic::firstOrCreate(['title' => $data['title']])->id),

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
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image_path')
                    ->label('تصویر سوال (اختیاری)')
                    ->disk('public')->directory('questions')->image()->openable()
                    ->live()
                    ->required(fn(Get $get) => blank($get('question_text')))
                    ->columnSpanFull(),

                Forms\Components\Repeater::make('options')
                    ->label('گزینه‌ها')
                    ->schema([
                        Forms\Components\TextInput::make('option_text')->label('متن گزینه')->required()->columnSpan(2),
                        Forms\Components\Toggle::make('is_correct')->label('پاسخ صحیح')->default(false),
                    ])
                    ->columns(3)
                    ->defaultItems(4)
                    ->minItems(2)
                    ->maxItems(6)
                    ->reorderable(false)
                    ->addActionLabel('افزودن گزینه')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('explanation')
                    ->label('توضیح پاسخ')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),

            ])
            ->columns(2)
            ->statePath('question');
    }

    /**
     * ذخیره‌ی سوال فعلی — مشترک بین دو دکمه.
     */
    protected function persistQuestion(): void
    {
        $context = $this->contextForm->getState();

        $questionData = $this->questionForm->getState();

        DB::transaction(function () use ($context, $questionData) {

            $question = Question::create([

                'content_item_id' => $context['content_item_id'] ?? null,

                'question_topic_id' => $context['question_topic_id'],

                'question_text' => $questionData['question_text'] ?? null,

                'image_path' => is_array($questionData['image_path'] ?? null)
                    ? collect($questionData['image_path'])->first()
                    : ($questionData['image_path'] ?? null),

                'difficulty' => $questionData['difficulty'],

                'explanation' => $questionData['explanation'],

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

                    'is_correct' => $option['is_correct'] ?? false,

                ]);
            }
        });

        $this->savedCount++;

        // فقط فرم سوال خالی می‌شود؛ مسیر آموزشی دست‌نخورده می‌ماند.
        $this->questionForm->fill();
    }

    public function saveAndContinue(): void
    {
        $this->persistQuestion();

        Notification::make()
            ->title('سوال ذخیره شد. می‌توانی سوال بعدی را بنویسی.')
            ->success()
            ->send();
    }

    public function saveAndExit(): void
    {
        $this->persistQuestion();

        Notification::make()
            ->title($this->savedCount.' سوال به‌صورت پیش‌نویس ذخیره شد.')
            ->success()
            ->send();

        $this->redirect(\App\Filament\Resources\QuestionResource::getUrl('index'));
    }
}
