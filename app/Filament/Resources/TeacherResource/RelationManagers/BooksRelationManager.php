<?php

namespace App\Filament\Resources\TeacherResource\RelationManagers;

use App\Models\App;
use App\Models\AppGradeSubject;
use App\Models\Book;
use App\Models\Grade;
use App\Models\Subject;
use App\Services\SettingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * کتاب‌های تدریسی معلم
 * --------------------------------------------------------------------
 * قبلاً هر معلم فقط یک کتاب می‌توانست داشته باشد (چون فرم اصلی
 * معلم مستقیم یک book_id داشت) — با انتخاب کتاب دوم، کتاب اول
 * عملاً از دید مخفی می‌شد (نه پاک، فقط دیگر نمایش داده نمی‌شد).
 * این RelationManager این محدودیت را برطرف می‌کند: هر معلم حالا
 * می‌تواند چند کتاب از پایه‌های مختلف داشته باشد، هرکدام با درصد
 * سهم مستقل خودش. فیلدهای اپلیکیشن/پایه/درس/کتاب هم مثل فرم
 * محتوا، قابل «انتخاب یا ایجاد» هستند.
 */
class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'teacherAssignments';

    protected static ?string $title = 'کتاب‌های تدریسی';

    protected static ?string $modelLabel = 'کتاب';

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Select::make('app_id')
                ->label('اپلیکیشن')
                ->options(App::where('is_active', true)->pluck('title', 'id'))
                ->searchable()
                ->preload()
                ->live()
                ->dehydrated(false)
                ->required()
                ->getOptionLabelUsing(fn($value) => App::find($value)?->title)
                ->createOptionForm([

                    Forms\Components\TextInput::make('title')
                        ->label('عنوان اپلیکیشن')
                        ->required(),

                ])
                ->createOptionUsing(function (array $data) {

                    $slug = Str::slug($data['title']);

                    $existing = App::where('slug', $slug)->first();

                    if ($existing) {

                        Notification::make()
                            ->title('این اپلیکیشن از قبل وجود دارد و انتخاب شد.')
                            ->warning()
                            ->send();

                        return $existing->id;
                    }

                    return App::create([
                        'title' => $data['title'],
                        'slug' => $slug,
                        'sort_order' => 1,
                        'is_active' => true,
                    ])->id;
                })
                ->afterStateUpdated(fn(Set $set) => $set('grade_id', null) ?: $set('subject_id', null) ?: $set('book_id', null)),

            Forms\Components\Select::make('grade_id')
                ->label('پایه')
                ->options(function (Get $get) {
                    if (! $get('app_id')) return [];
                    return Grade::whereHas('appGradeSubjects', fn($q) => $q->where('app_id', $get('app_id')))
                        ->orderBy('grade_number')
                        ->pluck('title', 'id');
                })
                ->searchable()
                ->preload()
                ->live()
                ->dehydrated(false)
                ->required()
                ->getOptionLabelUsing(fn($value) => Grade::find($value)?->title)
                ->createOptionForm([

                    Forms\Components\TextInput::make('title')
                        ->label('عنوان پایه')
                        ->required(),

                    Forms\Components\TextInput::make('grade_number')
                        ->label('شماره پایه')
                        ->numeric()
                        ->minValue(1)
                        ->required(),

                ])
                ->createOptionUsing(function (array $data, Get $get) {

                    $slug = Str::slug($data['title']);

                    $existing = Grade::where('grade_number', $data['grade_number'])->first();

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
                        'is_active' => true,
                    ]);

                    return $grade->id;
                })
                ->afterStateUpdated(fn(Set $set) => $set('subject_id', null) ?: $set('book_id', null)),

            Forms\Components\Select::make('subject_id')
                ->label('درس')
                ->options(function (Get $get) {
                    if (! $get('grade_id')) return [];
                    return Subject::whereHas('appGradeSubjects', fn($q) => $q
                        ->where('app_id', $get('app_id'))
                        ->where('grade_id', $get('grade_id')))
                        ->pluck('title', 'id');
                })
                ->searchable()
                ->preload()
                ->live()
                ->dehydrated(false)
                ->required()
                ->getOptionLabelUsing(fn($value) => Subject::find($value)?->title)
                ->createOptionForm([

                    Forms\Components\TextInput::make('title')
                        ->label('عنوان درس')
                        ->required(),

                ])
                ->createOptionUsing(function (array $data, Get $get) {

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

                    AppGradeSubject::firstOrCreate([
                        'app_id' => $get('app_id'),
                        'grade_id' => $get('grade_id'),
                        'subject_id' => $subject->id,
                    ]);

                    return $subject->id;
                })
                ->afterStateUpdated(fn(Set $set) => $set('book_id', null)),

            Forms\Components\Select::make('book_id')
                ->label('کتاب')
                ->options(function (Get $get) {
                    if (! $get('subject_id')) return [];
                    $ags = AppGradeSubject::where('app_id', $get('app_id'))
                        ->where('grade_id', $get('grade_id'))
                        ->where('subject_id', $get('subject_id'))
                        ->first();
                    if (! $ags) return [];
                    return Book::where('app_grade_subject_id', $ags->id)->pluck('title', 'id');
                })
                ->searchable()
                ->preload()
                ->getOptionLabelUsing(fn($value) => Book::find($value)?->title)
                ->required()
                ->createOptionForm([

                    Forms\Components\TextInput::make('title')
                        ->label('عنوان کتاب')
                        ->required(),

                ])
                ->createOptionUsing(function (array $data, Get $get) {

                    $ags = AppGradeSubject::where('app_id', $get('app_id'))
                        ->where('grade_id', $get('grade_id'))
                        ->where('subject_id', $get('subject_id'))
                        ->first();

                    $slug = Str::slug($data['title']);

                    $existing = Book::where('app_grade_subject_id', $ags->id)
                        ->where('slug', $slug)
                        ->first();

                    if ($existing) {

                        Notification::make()
                            ->title('این کتاب از قبل وجود دارد و انتخاب شد.')
                            ->warning()
                            ->send();

                        return $existing->id;
                    }

                    return Book::create([
                        'app_grade_subject_id' => $ags->id,
                        'title' => $data['title'],
                        'slug' => $slug,
                        'sort_order' => 1,
                        'is_active' => true,
                    ])->id;
                }),

            Forms\Components\TextInput::make('commission_percentage')
                ->label('درصد سهم معلم (پس از کسر کارمزد درگاه پرداخت)')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->default(fn() => app(SettingService::class)->defaultTeacherCommissionPercentage())
                ->suffix('%')
                ->helperText('این درصد روی مبلغ باقی‌مانده‌ی بعد از کسر کارمزد درگاه (زیبال/بازار/مایکت) اعمال می‌شود، نه روی قیمت کامل خرید.')
                ->required(),

            Forms\Components\Toggle::make('is_active')
                ->label('فعال')
                ->default(true),

        ]);
    }

    /**
     * موقع ویرایش یک تخصیص موجود، زنجیره‌ی بالا (اپ/پایه/درس) از
     * روی کتاب واقعی بازسازی می‌شود. نکته‌ی فنی مهم: چون این
     * فرم داخل یک اکشن جدولی (نه یک صفحه‌ی مستقل) است، متد
     * mutateFormDataBeforeFill خودکار صدا زده نمی‌شود — باید
     * مستقیم روی خودِ EditAction وصل شود.
     */
    protected function fillEditFormData(array $data): array
    {
        if (! empty($data['book_id'])) {

            $book = Book::with('appGradeSubject')->find($data['book_id']);

            if ($book && $book->appGradeSubject) {

                $data['app_id'] = $book->appGradeSubject->app_id;

                $data['grade_id'] = $book->appGradeSubject->grade_id;

                $data['subject_id'] = $book->appGradeSubject->subject_id;
            }
        }

        return $data;
    }

    protected function fillCreateFormData(array $data): array
    {
        $data['assigned_by'] = auth()->id();

        return $data;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('book.title')
            ->columns([

                Tables\Columns\TextColumn::make('book.title')
                    ->label('کتاب'),

                Tables\Columns\TextColumn::make('book.appGradeSubject.grade.title')
                    ->label('پایه'),

                Tables\Columns\TextColumn::make('book.appGradeSubject.subject.title')
                    ->label('درس'),

                Tables\Columns\TextColumn::make('commission_percentage')
                    ->label('درصد سهم معلم (بعد کسر کارمزد درگاه)')
                    ->formatStateUsing(fn($state) => $state.'٪'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('افزودن کتاب')
                    ->mutateFormDataUsing(fn(array $data) => $this->fillCreateFormData($data)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(fn(array $data) => $this->fillEditFormData($data)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
