<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\App;
use App\Models\AppGradeSubject;
use App\Models\Book;
use App\Models\Grade;
use App\Models\Plan;
use App\Models\Subject;
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
 * پلن‌ها (بسته‌های فروش)
 * --------------------------------------------------------------------
 * هر پلن مشخص می‌کند «با خرید این، چه چیزی باز می‌شود» — یا یک
 * پایه‌ی کامل (برای دبستان: پایه‌های ۱ تا ۶)، یا یک کتاب مشخص
 * (برای متوسطه: پایه‌های ۷ تا ۱۲). این تفاوت از طریق فیلد
 * «نوع دسترسی» تعیین می‌شود و در نهایت روی ستون‌های چندریختی
 * planable_type/planable_id ذخیره می‌گردد.
 */
class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'مدیریت مالی';

    protected static ?string $navigationLabel = 'پلن‌ها';

    protected static ?string $modelLabel = 'پلن';

    protected static ?string $pluralModelLabel = 'پلن‌ها';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('title')
                ->label('عنوان پلن')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('description')
                ->label('توضیحات')
                ->rows(2)
                ->columnSpanFull(),

            Forms\Components\Section::make('این پلن چه چیزی را باز می‌کند؟')

                ->columns(4)

                ->schema([

                    Forms\Components\Select::make('access_type')
                        ->label('نوع دسترسی')
                        ->options([
                            'grade' => 'کل یک پایه (دبستان)',
                            'book' => 'یک کتاب مشخص (متوسطه)',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {

                            $set('app_id', null);
                            $set('grade_id', null);
                            $set('subject_id', null);
                            $set('book_id', null);
                        }),

                    // --- حالت «کل پایه» ---
                    Forms\Components\Select::make('grade_only_id')
                        ->label('پایه')
                        ->options(Grade::pluck('title', 'id'))
                        ->visible(fn(Get $get) => $get('access_type') === 'grade')
                        ->required(fn(Get $get) => $get('access_type') === 'grade')
                        ->columnSpan(3),

                    // --- حالت «یک کتاب مشخص» — زنجیره‌ی آشنا ---
                    Forms\Components\Select::make('app_id')
                        ->label('اپلیکیشن')
                        ->options(App::where('is_active', true)->pluck('title', 'id'))
                        ->live()
                        ->dehydrated(false)
                        ->visible(fn(Get $get) => $get('access_type') === 'book')
                        ->required(fn(Get $get) => $get('access_type') === 'book')
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
                        ->visible(fn(Get $get) => $get('access_type') === 'book')
                        ->required(fn(Get $get) => $get('access_type') === 'book')
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
                        ->visible(fn(Get $get) => $get('access_type') === 'book')
                        ->required(fn(Get $get) => $get('access_type') === 'book')
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
                        ->visible(fn(Get $get) => $get('access_type') === 'book')
                        ->required(fn(Get $get) => $get('access_type') === 'book')
                        ->columnSpan(1),

                ]),

            Forms\Components\Section::make('قیمت و مدت')

                ->columns(2)

                ->schema([

                    Forms\Components\TextInput::make('price')
                        ->label('قیمت (تومان)')
                        ->numeric()
                        ->required(),

                    Forms\Components\TextInput::make('discount_price')
                        ->label('قیمت با تخفیف (تومان، اختیاری)')
                        ->numeric(),

                    Forms\Components\Select::make('purchase_type')
                        ->label('نوع خرید')
                        ->options([
                            'one_time' => 'یک‌بار پرداخت',
                            'subscription' => 'اشتراکی',
                        ])
                        ->required()
                        ->default('one_time'),

                    Forms\Components\TextInput::make('duration_days')
                        ->label('مدت دسترسی (روز)')
                        ->numeric()
                        ->helperText('خالی بگذارید یعنی دسترسی دائمی است.'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('فعال')
                        ->default(true),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('ترتیب نمایش')
                        ->numeric()
                        ->default(1),

                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('sort_order')

            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),

                Tables\Columns\TextColumn::make('planable_type')
                    ->label('نوع دسترسی')
                    ->formatStateUsing(fn($state) => match ($state) {
                        \App\Models\Grade::class => 'کل پایه',
                        \App\Models\Book::class => 'یک کتاب',
                        default => $state,
                    })
                    ->badge(),

                Tables\Columns\TextColumn::make('planable.title')
                    ->label('مورد'),

                Tables\Columns\TextColumn::make('price')
                    ->label('قیمت')
                    ->formatStateUsing(fn($state) => number_format($state).' تومان'),

                Tables\Columns\TextColumn::make('discount_price')
                    ->label('با تخفیف')
                    ->formatStateUsing(fn($state) => $state ? number_format($state).' تومان' : '—'),

                Tables\Columns\TextColumn::make('purchase_type')
                    ->label('نوع')
                    ->formatStateUsing(fn($state) => $state === 'subscription' ? 'اشتراکی' : 'یک‌بار'),

                Tables\Columns\TextColumn::make('duration_days')
                    ->label('مدت')
                    ->formatStateUsing(fn($state) => $state ? "{$state} روز" : 'دائمی'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

            ])

            ->filters([

                Tables\Filters\SelectFilter::make('purchase_type')
                    ->label('نوع خرید')
                    ->options([
                        'one_time' => 'یک‌بار پرداخت',
                        'subscription' => 'اشتراکی',
                    ]),

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

                ]),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
