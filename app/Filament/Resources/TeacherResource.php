<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Filament\Resources\TeacherResource\RelationManagers;
use App\Models\App;
use App\Models\AppGradeSubject;
use App\Models\Book;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use App\Services\SettingService;
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

                    Forms\Components\TextInput::make('mobile')
                        ->label('شماره موبایل')
                        ->required()
                        ->tel()
                        ->maxLength(11)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Set $set) {

                            $existingUser = User::where('mobile', $state)
                                ->whereNull('deleted_at')
                                ->first();

                            if ($existingUser && $existingUser->name) {

                                $set('name', $existingUser->name);
                            }
                        })
                        ->helperText('اگر این شماره از قبل توی سیستم وجود داشته باشد، همان حساب به معلم تبدیل می‌شود، نام آن خودکار پر می‌شود، و نیازی به وارد کردن رمز نیست.'),

                    Forms\Components\TextInput::make('name')
                        ->label('نام معلم')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('password')
                        ->label('رمز اولیه')
                        ->password()
                        ->revealable()
                        ->required(function (string $operation, Get $get) {

                            if ($operation !== 'create') {
                                return false;
                            }

                            $existingUser = User::where('mobile', $get('mobile'))
                                ->whereNull('deleted_at')
                                ->exists();

                            return ! $existingUser;
                        })
                        ->dehydrated(fn($state) => filled($state))
                        ->helperText('در حالت ویرایش (یا وقتی این شماره از قبل وجود دارد)، اگر خالی بماند رمز قبلی تغییر نمی‌کند.'),

                    Forms\Components\Toggle::make('must_change_password')
                        ->label('اجبار به تغییر رمز در اولین ورود')
                        ->default(true),

                    Forms\Components\Toggle::make('is_active')
                        ->label('فعال')
                        ->default(true),

                ]),

            Forms\Components\Section::make('کتاب اول (اختیاری)')

                ->description('اگر همین الان می‌خوای یه کتاب هم بهش بدی، اینجا پرش کن. برای کتاب دوم به بعد (یا اگه الان پرش نکنی)، بعد از ایجاد معلم، از تب «کتاب‌های تدریسی» پایین صفحه‌ی ویرایش همین معلم استفاده کن.')

                ->columns(4)

                ->visible(fn(string $operation) => $operation === 'create')

                ->schema([

                    Forms\Components\Select::make('first_app_id')
                        ->label('اپلیکیشن')
                        ->options(App::where('is_active', true)->pluck('title', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->dehydrated(false)
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
                        ->afterStateUpdated(fn(Set $set) => $set('first_grade_id', null) ?: $set('first_subject_id', null) ?: $set('first_book_id', null)),

                    Forms\Components\Select::make('first_grade_id')
                        ->label('پایه')
                        ->options(function (Get $get) {
                            if (! $get('first_app_id')) return [];
                            return Grade::whereHas('appGradeSubjects', fn($q) => $q->where('app_id', $get('first_app_id')))
                                ->orderBy('grade_number')
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->dehydrated(false)
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
                        ->createOptionUsing(function (array $data) {

                            $slug = Str::slug($data['title']);

                            $existing = Grade::where('grade_number', $data['grade_number'])->first();

                            if ($existing) {

                                Notification::make()
                                    ->title('این پایه از قبل وجود دارد و انتخاب شد.')
                                    ->warning()
                                    ->send();

                                return $existing->id;
                            }

                            return Grade::create([
                                'title' => $data['title'],
                                'slug' => $slug,
                                'grade_number' => $data['grade_number'],
                                'is_active' => true,
                            ])->id;
                        })
                        ->afterStateUpdated(fn(Set $set) => $set('first_subject_id', null) ?: $set('first_book_id', null)),

                    Forms\Components\Select::make('first_subject_id')
                        ->label('درس')
                        ->options(function (Get $get) {
                            if (! $get('first_grade_id')) return [];
                            return Subject::whereHas('appGradeSubjects', fn($q) => $q
                                ->where('app_id', $get('first_app_id'))
                                ->where('grade_id', $get('first_grade_id')))
                                ->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->dehydrated(false)
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
                                'app_id' => $get('first_app_id'),
                                'grade_id' => $get('first_grade_id'),
                                'subject_id' => $subject->id,
                            ]);

                            return $subject->id;
                        })
                        ->afterStateUpdated(fn(Set $set) => $set('first_book_id', null)),

                    Forms\Components\Select::make('first_book_id')
                        ->label('کتاب')
                        ->options(function (Get $get) {
                            if (! $get('first_subject_id')) return [];
                            $ags = AppGradeSubject::where('app_id', $get('first_app_id'))
                                ->where('grade_id', $get('first_grade_id'))
                                ->where('subject_id', $get('first_subject_id'))
                                ->first();
                            if (! $ags) return [];
                            return Book::where('app_grade_subject_id', $ags->id)->pluck('title', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->getOptionLabelUsing(fn($value) => Book::find($value)?->title)
                        ->createOptionForm([

                            Forms\Components\TextInput::make('title')
                                ->label('عنوان کتاب')
                                ->required(),

                        ])
                        ->createOptionUsing(function (array $data, Get $get) {

                            $ags = AppGradeSubject::where('app_id', $get('first_app_id'))
                                ->where('grade_id', $get('first_grade_id'))
                                ->where('subject_id', $get('first_subject_id'))
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

                    Forms\Components\TextInput::make('first_commission_percentage')
                        ->label('درصد سهم معلم (پس از کسر کارمزد درگاه پرداخت)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(fn() => app(SettingService::class)->defaultTeacherCommissionPercentage())
                        ->suffix('%')
                        ->columnSpan(2)
                        ->helperText('این درصد روی مبلغ باقی‌مانده‌ی بعد از کسر کارمزد درگاه (زیبال/بازار/مایکت) اعمال می‌شود، نه روی قیمت کامل خرید.'),

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

                Tables\Columns\TextColumn::make('books_by_grade')
                    ->label('پایه و کتاب')
                    ->getStateUsing(function (User $record) {

                        $assignments = $record->teacherAssignments()
                            ->where('is_active', true)
                            ->with('book.appGradeSubject.grade')
                            ->get()
                            ->filter(fn($a) => $a->book?->appGradeSubject?->grade)
                            // مرتب‌سازی بر اساس شماره‌ی واقعی پایه
                            // (نه اسمش)، تا «چهارم» همیشه قبل از
                            // «ششم» بیاید، نه بر اساس هرترتیبی که
                            // توی دیتابیس ذخیره شده.
                            ->sortBy(fn($a) => $a->book->appGradeSubject->grade->grade_number);

                        if ($assignments->isEmpty()) {
                            return '—';
                        }

                        return $assignments
                            ->map(fn($a) => $a->book->appGradeSubject->grade->title.': '.$a->book->title)
                            ->implode(' | ');
                    })
                    ->wrap(),

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
        return [
            RelationManagers\BooksRelationManager::class,
        ];
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
