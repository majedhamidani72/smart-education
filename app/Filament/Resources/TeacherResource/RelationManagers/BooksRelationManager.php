<?php

namespace App\Filament\Resources\TeacherResource\RelationManagers;

use App\Models\App;
use App\Models\AppGradeSubject;
use App\Models\Book;
use App\Models\Grade;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * کتاب‌های تدریسی معلم
 * --------------------------------------------------------------------
 * قبلاً هر معلم فقط یک کتاب می‌توانست داشته باشد (چون فرم اصلی
 * معلم مستقیم یک book_id داشت) — با انتخاب کتاب دوم، کتاب اول
 * عملاً از دید مخفی می‌شد (نه پاک، فقط دیگر نمایش داده نمی‌شد).
 * این RelationManager این محدودیت را برطرف می‌کند: هر معلم حالا
 * می‌تواند چند کتاب از پایه‌های مختلف داشته باشد، هرکدام با درصد
 * سهم مستقل خودش.
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
                ->live()
                ->dehydrated(false)
                ->required()
                ->afterStateUpdated(fn(Set $set) => $set('grade_id', null) ?: $set('subject_id', null) ?: $set('book_id', null)),

            Forms\Components\Select::make('grade_id')
                ->label('پایه')
                ->options(function (Get $get) {
                    if (! $get('app_id')) return [];
                    return Grade::whereHas('appGradeSubjects', fn($q) => $q->where('app_id', $get('app_id')))
                        ->orderBy('grade_number')
                        ->pluck('title', 'id');
                })
                ->live()
                ->dehydrated(false)
                ->required()
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
                ->live()
                ->dehydrated(false)
                ->required()
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
                ->getOptionLabelUsing(fn($value) => Book::find($value)?->title)
                ->required(),

            Forms\Components\Fieldset::make('درصد سهم معلم، به تفکیک درگاه فروش')

                ->columns(3)

                ->schema([

                    Forms\Components\TextInput::make('commission_percentage_zibal')
                        ->label('زیبال (سایت/اپ مستقیم)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(30)
                        ->suffix('%')
                        ->required(),

                    Forms\Components\TextInput::make('commission_percentage_bazaar')
                        ->label('کافه‌بازار')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(30)
                        ->suffix('%')
                        ->required(),

                    Forms\Components\TextInput::make('commission_percentage_myket')
                        ->label('مایکت')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(30)
                        ->suffix('%')
                        ->required(),

                ]),

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

                Tables\Columns\TextColumn::make('commission_percentage_zibal')
                    ->label('زیبال')
                    ->formatStateUsing(fn($state) => $state.'٪'),

                Tables\Columns\TextColumn::make('commission_percentage_bazaar')
                    ->label('بازار')
                    ->formatStateUsing(fn($state) => $state.'٪'),

                Tables\Columns\TextColumn::make('commission_percentage_myket')
                    ->label('مایکت')
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
