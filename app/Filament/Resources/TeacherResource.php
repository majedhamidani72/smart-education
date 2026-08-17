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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
 * برخلاف فرم «ایجاد محتوا»، اینجا امکان «ایجاد» اپلیکیشن/پایه/
 * درس/کتاب جدید وجود ندارد — چون طبق تصمیم پروژه، ساختار
 * آموزشی را فقط سوپرادمین/ادمین از داخل «ایجاد محتوا» می‌سازد؛
 * این فرم فقط از میان ساختار از قبل موجود انتخاب می‌کند.
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
                        ->unique(
                            table: User::class,
                            column: 'mobile',
                            ignoreRecord: true,
                            modifyRuleUsing: fn($rule) => $rule->whereNull('deleted_at'),
                        ),

                    Forms\Components\TextInput::make('password')
                        ->label('رمز اولیه')
                        ->password()
                        ->revealable()
                        ->required(fn(string $operation) => $operation === 'create')
                        ->dehydrated(fn($state) => filled($state))
                        ->helperText('در حالت ویرایش، اگر خالی بماند رمز قبلی تغییر نمی‌کند.'),

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
                        ->live()
                        ->dehydrated(false)
                        ->required()
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
                        ->live()
                        ->dehydrated(false)
                        ->required()
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
                        ->live()
                        ->dehydrated(false)
                        ->required()
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
                        ->required()
                        ->helperText('معلم فقط به کتابی که اینجا مشخص می‌شود دسترسی خواهد داشت.'),

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
