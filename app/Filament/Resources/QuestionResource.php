<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\App;
use App\Models\AppGradeSubject;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\ContentItem;
use App\Models\Grade;
use App\Models\QuestionTopic;
use App\Models\Section;
use App\Models\Subject;
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

/**
 * بانک سوالات
 * --------------------------------------------------------------------
 * هر سوال (اختیاری) به یک محتوای آموزشی مشخص وصل می‌شود — همان
 * پیوندی که بعداً برای پیشنهاد «مرور کلیپ» بعد از جواب غلط در
 * گزارش آزمون استفاده خواهد شد. برای همین، انتخاب محتوا از طریق
 * همان زنجیره‌ی آشنای اپلیکیشن→پایه→درس→کتاب→فصل→بخش انجام
 * می‌شود، دقیقاً مثل فرم «ایجاد محتوا».
 *
 * گزینه‌های هر سوال با ->relationship('options') به‌صورت خودکار
 * توسط Filament مدیریت می‌شوند (نه دستی مثل ویدئو/PDF) — چون
 * options یک رابطه‌ی ساده و استاندارد HasMany است و از دردسرهای
 * قبلی (ستون‌های گم‌شده، مسیر فایل و...) در امان است.
 */
class QuestionResource extends Resource
{
    protected static ?string $model = \App\Models\Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'آزمون آنلاین';

    protected static ?string $navigationLabel = 'بانک سوالات';

    protected static ?string $modelLabel = 'سوال';

    protected static ?string $pluralModelLabel = 'سوالات';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $isTeacher = auth()->user()?->hasRole('Teacher');

        return $form->schema([

            Forms\Components\Section::make('محتوای مرتبط (اختیاری)')

                ->description('برای اینکه بعد از جواب غلط، دانش‌آموز بتواند کلیپ مربوطه را مرور کند.')

                ->columns(3)

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
                            $set('section_id', null);
                            $set('content_item_id', null);
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
                                    fn($query) => $query->where('app_id', $get('app_id'))
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
                            $set('section_id', null);
                            $set('content_item_id', null);
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
                                    fn($query) => $query
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
                        ->afterStateUpdated(function (Set $set) {

                            $set('book_id', null);
                            $set('chapter_id', null);
                            $set('section_id', null);
                            $set('content_item_id', null);
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
                                ->where('app_grade_subject_id', $appGradeSubject->id)
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(function (Set $set) {

                            $set('chapter_id', null);
                            $set('section_id', null);
                            $set('content_item_id', null);
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
                        ->afterStateUpdated(function (Set $set) {

                            $set('section_id', null);
                            $set('content_item_id', null);
                        }),

                    Forms\Components\Select::make('section_id')
                        ->label('بخش')
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
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(fn(Set $set) => $set('content_item_id', null)),

                    Forms\Components\Select::make('content_item_id')
                        ->label('محتوای آموزشی (کلیپ)')
                        ->options(function (Get $get) {

                            if (! $get('section_id')) {
                                return [];
                            }

                            return ContentItem::query()
                                ->where('section_id', $get('section_id'))
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->getOptionLabelUsing(fn($value) => ContentItem::find($value)?->title)
                        ->columnSpan(3),

                ]),

            Forms\Components\Section::make('متن سوال')

                ->schema([

                    Forms\Components\Select::make('question_topic_id')
                        ->label('موضوع سوال')
                        ->options(QuestionTopic::pluck('title', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([

                            Forms\Components\TextInput::make('title')
                                ->label('عنوان موضوع')
                                ->required(),

                        ])
                        ->createOptionUsing(function (array $data) {

                            $existing = QuestionTopic::where('title', $data['title'])->first();

                            if ($existing) {

                                Notification::make()
                                    ->title('این موضوع از قبل وجود دارد و انتخاب شد.')
                                    ->warning()
                                    ->send();

                                return $existing->id;
                            }

                            return QuestionTopic::create([
                                'title' => $data['title'],
                            ])->id;
                        }),

                    Forms\Components\Select::make('difficulty')
                        ->label('سطح سختی')
                        ->options([
                            'easy' => 'آسان',
                            'medium' => 'متوسط',
                            'hard' => 'سخت',
                        ])
                        ->required()
                        ->default('medium'),

                    Forms\Components\Textarea::make('question_text')
                        ->label('متن سوال')
                        ->live()
                        ->required(fn(Get $get) => blank($get('image_path')))
                        ->helperText('حداقل یکی از متن سوال یا تصویر سوال باید پر شود.')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('image_path')
                        ->label('تصویر سوال')
                        ->disk('public')
                        ->directory('questions')
                        ->image()
                        ->openable()
                        ->live()
                        ->required(fn(Get $get) => blank($get('question_text')))
                        ->helperText('حداقل یکی از متن سوال یا تصویر سوال باید پر شود.')
                        ->columnSpanFull(),

                ])
                ->columns(2),

            Forms\Components\Section::make('گزینه‌ها')

                ->description('گزینه‌ای که تیک «پاسخ صحیح» دارد، به‌عنوان جواب درست ثبت می‌شود.')

                ->schema([

                    Forms\Components\Repeater::make('options')
                        ->relationship()
                        ->label('')
                        ->schema([

                            Forms\Components\TextInput::make('option_text')
                                ->label('متن گزینه')
                                ->required()
                                ->columnSpan(2),

                            Forms\Components\FileUpload::make('image_path')
                                ->label('تصویر گزینه (اختیاری)')
                                ->disk('public')
                                ->directory('question-options')
                                ->image()
                                ->openable(),

                            Forms\Components\Toggle::make('is_correct')
                                ->label('پاسخ صحیح')
                                ->default(false),

                        ])
                        ->columns(4)
                        ->defaultItems(4)
                        ->minItems(2)
                        ->maxItems(6)
                        ->reorderable(false)
                        ->addActionLabel('افزودن گزینه'),

                ]),

            Forms\Components\Section::make('توضیح پاسخ')

                ->description('این توضیح بعد از آزمون، در صورت جواب غلط، به دانش‌آموز نمایش داده می‌شود.')

                ->schema([

                    Forms\Components\Textarea::make('explanation')
                        ->label('توضیح')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),

                ]),

            Forms\Components\Section::make('وضعیت')

                ->columns(2)

                ->schema([

                    Forms\Components\Toggle::make('is_active')
                        ->label('فعال')
                        ->default(true),

                    Forms\Components\Hidden::make('created_by')
                        ->default(fn() => auth()->id()),

                    // معلم نمی‌تواند مستقیم وضعیت را تغییر دهد؛ هر
                    // سوال تازه در صف «در انتظار بررسی» می‌رود.
                    Forms\Components\Hidden::make('status')
                        ->default('pending'),

                    Forms\Components\Select::make('status')
                        ->label('وضعیت')
                        ->options([
                            'pending' => 'در انتظار بررسی',
                            'approved' => 'تأیید شده',
                            'rejected' => 'رد شده',
                        ])
                        ->live()
                        ->visible(fn() => ! $isTeacher),

                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('دلیل رد')
                        ->rows(2)
                        ->visible(fn(Get $get) => ! $isTeacher && $get('status') === 'rejected')
                        ->required(fn(Get $get) => $get('status') === 'rejected')
                        ->columnSpanFull(),

                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultGroup('content_item_id')
            ->groups([

                Tables\Grouping\Group::make('content_item_id')
                    ->label('محتوا')
                    ->getTitleFromRecordUsing(function ($record) {

                        $item = $record->contentItem;

                        if (! $item) {
                            return 'بدون محتوای مشخص';
                        }

                        $chapter = $item->chapter;

                        $section = $item->section;

                        $path = collect([

                            $chapter?->book?->title,

                            $chapter?->title,

                            $section?->title,

                        ])->filter()->implode(' > ');

                        return ($path ? $path.' — ' : '').$item->title;
                    })
                    ->collapsible(),

            ])
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('question_text')
                    ->label('متن سوال')
                    ->limit(60)
                    ->searchable(),

                Tables\Columns\TextColumn::make('topic.title')
                    ->label('موضوع')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('difficulty')
                    ->label('سطح سختی')
                    ->colors([
                        'success' => 'easy',
                        'warning' => 'medium',
                        'danger' => 'hard',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'easy' => 'آسان',
                        'medium' => 'متوسط',
                        'hard' => 'سخت',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('contentItem.title')
                    ->label('محتوای مرتبط')
                    ->placeholder('—')
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('وضعیت')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'draft' => 'پیش نویس (ارسال نشده)',
                        'pending' => 'در انتظار بررسی',
                        'approved' => 'تأیید شده',
                        'rejected' => 'رد شده',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('ایجادکننده'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn($state) => \App\Support\Jalali::format($state)),

            ])
            ->filters([

                Tables\Filters\SelectFilter::make('question_topic_id')
                    ->label('موضوع')
                    ->options(QuestionTopic::pluck('title', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('difficulty')
                    ->label('سطح سختی')
                    ->options([
                        'easy' => 'آسان',
                        'medium' => 'متوسط',
                        'hard' => 'سخت',
                    ]),

                Tables\Filters\SelectFilter::make('created_by')
                    ->label('ایجادکننده')
                    ->relationship('creator', 'name')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'pending' => 'در انتظار بررسی',
                        'approved' => 'تأیید شده',
                        'rejected' => 'رد شده',
                    ]),

                Tables\Filters\TrashedFilter::make(),

            ])
            ->actions([

                // معلم فقط سوالات «پیش‌نویس» خودش را می‌تواند
                // «ارسال برای بررسی» کند — از این لحظه به بعد،
                // دیگر قابل ویرایش مستقیم نیست و در صف تایید ادمین
                // قرار می‌گیرد.
                Tables\Actions\Action::make('submitForReview')
                    ->label('ارسال برای بررسی')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn($record) =>
                        $record->status === 'draft'
                        && $record->created_by === auth()->id())
                    ->requiresConfirmation()
                    ->modalDescription('بعد از ارسال، دیگر نمی‌توانی این سوال را ویرایش کنی تا ادمین بررسی‌اش کند.')
                    ->action(function ($record) {

                        $record->update(['status' => 'pending']);

                        Notification::make()
                            ->title('سوال برای بررسی ارسال شد.')
                            ->success()
                            ->send();
                    }),

                // کلیک روی بادج وضعیت، بدون رفتن به صفحه‌ی ویرایش،
                // مستقیم یه مودال کوچیک برای تایید/رد باز می‌کنه —
                // برای بررسی سریعِ تک‌تک سوالات داخل یک گروه.
                Tables\Actions\Action::make('changeStatus')
                    ->label('تغییر وضعیت')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn() => auth()->user()?->hasRole('SuperAdmin') || auth()->user()?->hasRole('Admin'))
                    ->form([

                        Forms\Components\Select::make('status')
                            ->label('وضعیت')
                            ->options([
                                'pending' => 'در انتظار بررسی',
                                'approved' => 'تأیید شده',
                                'rejected' => 'رد شده',
                            ])
                            ->live()
                            ->required(),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('دلیل رد')
                            ->rows(2)
                            ->visible(fn(Get $get) => $get('status') === 'rejected')
                            ->required(fn(Get $get) => $get('status') === 'rejected'),

                    ])
                    ->fillForm(fn($record) => [
                        'status' => $record->status,
                        'rejection_reason' => $record->rejection_reason,
                    ])
                    ->action(function ($record, array $data) {

                        $record->update([

                            'status' => $data['status'],

                            'rejection_reason' => $data['status'] === 'rejected'
                                ? $data['rejection_reason']
                                : null,

                        ]);

                        Notification::make()
                            ->title('وضعیت سوال به‌روزرسانی شد.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),

                Tables\Actions\RestoreAction::make(),

                Tables\Actions\ForceDeleteAction::make(),

            ])
            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([

                    // معلم می‌تواند چندتا سوال پیش‌نویس خودش را
                    // (مثلاً همه‌ی سوالاتی که برای یک بخش نوشته)
                    // با یک کلیک، همه باهم برای بررسی بفرستد.
                    Tables\Actions\BulkAction::make('submitForReviewBulk')
                        ->label('ارسال برای بررسی')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalDescription('بعد از ارسال، دیگر نمی‌توانی این سوالات را ویرایش کنی تا ادمین بررسی‌شان کند.')
                        ->action(function ($records) {

                            $count = 0;

                            foreach ($records as $record) {

                                if ($record->status !== 'draft' || $record->created_by !== auth()->id()) {
                                    continue;
                                }

                                $record->update(['status' => 'pending']);

                                $count++;
                            }

                            Notification::make()
                                ->title($count.' سوال برای بررسی ارسال شد.')
                                ->success()
                                ->send();
                        }),

                    // با گروه‌بندی جدول، کاربر می‌تواند از طریق
                    // چک‌باکس بالای هر گروه، همه‌ی سوالات همان
                    // محتوا (بخش/فصل) را یک‌جا انتخاب کند و همین‌جا
                    // با یک کلیک وضعیت همه را باهم عوض کند.
                    Tables\Actions\BulkAction::make('changeStatusBulk')
                        ->label('تغییر وضعیت دسته‌جمعی')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn() => auth()->user()?->hasRole('SuperAdmin') || auth()->user()?->hasRole('Admin'))
                        ->requiresConfirmation()
                        ->form([

                            Forms\Components\Select::make('status')
                                ->label('وضعیت جدید')
                                ->options([
                                    'approved' => 'تأیید شده',
                                    'rejected' => 'رد شده',
                                    'pending' => 'در انتظار بررسی',
                                ])
                                ->live()
                                ->required(),

                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('دلیل رد (برای همه‌ی سوالات انتخاب‌شده)')
                                ->rows(2)
                                ->visible(fn(Get $get) => $get('status') === 'rejected')
                                ->required(fn(Get $get) => $get('status') === 'rejected'),

                        ])
                        ->action(function ($records, array $data) {

                            foreach ($records as $record) {

                                $record->update([

                                    'status' => $data['status'],

                                    'rejection_reason' => $data['status'] === 'rejected'
                                        ? $data['rejection_reason']
                                        : null,

                                ]);
                            }

                            Notification::make()
                                ->title('وضعیت '.count($records).' سوال به‌روزرسانی شد.')
                                ->success()
                                ->send();
                        }),

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

            'index' => Pages\ListQuestions::route('/'),

            'create' => Pages\CreateQuestion::route('/create'),

            'edit' => Pages\EditQuestion::route('/{record}/edit'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with([
                'contentItem.chapter.book',
                'contentItem.section',
            ]);

        $user = auth()->user();

        // معلم فقط سوالات خودش را می‌بیند، نه سوالات معلم‌های
        // دیگر را.
        if ($user?->hasRole('Teacher') && ! $user->hasRole('SuperAdmin') && ! $user->hasRole('Admin')) {

            return $query->where('created_by', $user->id);
        }

        return $query;
    }
}
