<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\App;
use App\Models\AppGradeSubject;
use App\Models\Book;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
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

/**
 * مدیریت معلمان
 * --------------------------------------------------------------------
 * طبق تصمیم پروژه، برخلاف قبل (که ساخت کاربر معلم و اختصاص کتاب
 * به او دو مرحله‌ی جدا در دو Resource مختلف بود)، این فرم هر دو
 * کار را همزمان انجام می‌دهد: همان لحظه که سوپرادمین/ادمین معلم
 * را می‌سازد، اپلیکیشن/پایه/درس/کتابی که این معلم اجازه‌ی کار
 * روی آن را دارد هم مشخص می‌شود.
 *
 * فیلدهای اپلیکیشن/پایه/درس فقط برای فیلتر کردن گزینه‌های
 * «کتاب» هستند و روی جدول users ذخیره نمی‌شوند؛ آنچه واقعاً
 * ذخیره می‌شود یک رکورد TeacherAssignment است (نگاه کنید به
 * Pages\CreateTeacher و Pages\EditTeacher).
 *
 * برخلاف فرم «ایجاد محتوا»، اینجا هم امکان «ایجاد» اپلیکیشن/
 * پایه/درس/کتاب جدید وجود دارد (چون این فرم هم فقط در اختیار
 * سوپرادمین/ادمین است، نه معلم). اگر چیزی که کاربر می‌خواهد
 * بسازد از قبل موجود باشد، به‌جای خطای یکتایی، یک پیام هشدار
 * نشان داده می‌شود و همان رکورد موجود انتخاب می‌شود.
 */
class TeacherResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'مدیریت کاربران';

    protected static ?string $navigationLabel = 'معلم';

    protected static ?string $modelLabel = 'معلم';

    protected static ?string $pluralModelLabel = 'معلمان';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()
            &&
            (
                auth()->user()->hasRole('SuperAdmin')
                ||
                auth()->user()->hasRole('Admin')
            );
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('اطلاعات حساب معلم')

                ->columns(2)

                ->schema([

                    Forms\Components\TextInput::make('name')
                        ->label('نام معلم')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('mobile')
                        ->label('شماره موبایل')
                        ->required()
                        ->tel()
                        ->maxLength(11)
                        ->live(onBlur: true)
                        ->helperText('اگر این شماره از قبل توی سیستم وجود داشته باشد، همان حساب به معلم تبدیل می‌شود و نیازی به وارد کردن رمز نیست.'),

                    Forms\Components\TextInput::make('password')
                        ->label('رمز اولیه')
                        ->password()
                        ->revealable()
                        ->required(function (string $operation, Get $get) {

                            if ($operation !== 'create') {
                                return false;
                            }

                            // اگر این موبایل از قبل (به‌صورت فعال) در
                            // سیستم وجود داشته باشد، رمز اجباری
                            // نیست — رمز قبلی همان کاربر دست‌نخورده
                            // می‌ماند.
                            $existingUser = User::where('mobile', $get('mobile'))
                                ->whereNull('deleted_at')
                                ->exists();

                            return ! $existingUser;
                        })
                        ->dehydrated(fn($state) => filled($state))
                        ->helperText('در حالت ویرایش (یا وقتی این شماره از قبل وجود دارد)، اگر خالی بماند رمز قبلی تغییر نمی‌کند.'),

                    Forms\Components\Toggle::make('must_change_password')
                        ->label('اجبار تغییر رمز در اولین ورود')
                        ->default(true),

                    Forms\Components\Toggle::make('is_active')
                        ->label('فعال')
                        ->default(true),

                ]),

            Forms\Components\Section::make('دسترسی آموزشی معلم')

                ->columns(2)

                ->description('کتابی که این معلم اجازه‌ی مدیریت محتوای آن را دارد.')

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
                        ->getOptionLabelUsing(fn($value) => App::find($value)?->title)
                        ->live()
                        ->dehydrated(false)
                        ->required()
                        ->createOptionForm([

                            Forms\Components\TextInput::make('title')
                                ->label('عنوان اپلیکیشن')
                                ->required(),

                            Forms\Components\Toggle::make('is_active')
                                ->label('فعال')
                                ->default(true),

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

                            $app = App::create([
                                'title' => $data['title'],
                                'slug' => $slug,
                                'is_active' => $data['is_active'],
                                'sort_order' => 1,
                            ]);

                            return $app->id;
                        })
                        ->afterStateUpdated(function (Set $set) {

                            $set('grade_id', null);
                            $set('subject_id', null);
                            $set('book_id', null);
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
                        ->getOptionLabelUsing(fn($value) => Grade::find($value)?->title)
                        ->live()
                        ->dehydrated(false)
                        ->required()
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

                            // توجه: این پایه هنوز به هیچ اپلیکیشنی متصل
                            // نیست؛ اتصال (app_grade_subjects) در مرحله‌ی
                            // انتخاب/ایجاد «درس» کامل می‌شود.
                            return $grade->id;
                        })
                        ->afterStateUpdated(function (Set $set) {

                            $set('subject_id', null);
                            $set('book_id', null);
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
                        ->getOptionLabelUsing(fn($value) => Subject::find($value)?->title)
                        ->live()
                        ->dehydrated(false)
                        ->required()
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

                            // پیوند سه‌طرفه‌ی اپلیکیشن+پایه+درس همین‌جا
                            // کامل می‌شود.
                            AppGradeSubject::firstOrCreate([
                                'app_id' => $get('app_id'),
                                'grade_id' => $get('grade_id'),
                                'subject_id' => $subject->id,
                            ]);

                            return $subject->id;
                        })
                        ->afterStateUpdated(function (Set $set) {

                            $set('book_id', null);
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
                        ->getOptionLabelUsing(fn($value) => Book::find($value)?->title)
                        ->required()
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
                        ->helperText('معلم فقط به کتابی که اینجا مشخص می‌شود دسترسی خواهد داشت.'),

                    Forms\Components\TextInput::make('commission_percentage')
                        ->label('درصد سهم معلم از فروش این کتاب')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(30)
                        ->suffix('%')
                        ->helperText('برای هر معلم/کتاب می‌تواند متفاوت باشد.')
                        ->required(),

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

                Tables\Columns\TextColumn::make('name')
                    ->label('نام')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('موبایل')
                    ->searchable(),

                Tables\Columns\TextColumn::make('teacherAssignments.book.title')
                    ->label('کتاب')
                    ->getStateUsing(function (User $record) {

                        return $record->teacherAssignments()
                            ->where('is_active', true)
                            ->with('book')
                            ->latest()
                            ->first()?->book?->title ?? '—';
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->dateTime('Y/m/d H:i'),

            ])
            ->filters([

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('فعال'),

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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [

            'index' => Pages\ListTeachers::route('/'),

            'create' => Pages\CreateTeacher::route('/create'),

            'edit' => Pages\EditTeacher::route('/{record}/edit'),

        ];
    }

    /**
     * فقط کاربرانی که نقش Teacher دارند در این Resource دیده
     * می‌شوند.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->role('Teacher');
    }
}
