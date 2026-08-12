<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Filament\Resources\QuizResource\RelationManagers\QuestionsRelationManager;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'مدیریت آزمون';

    protected static ?string $navigationLabel = 'آزمون‌ها';

    protected static ?string $modelLabel = 'آزمون';

    protected static ?string $pluralModelLabel = 'آزمون‌ها';


    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('title')
                ->label('عنوان آزمون')
                ->required()
                ->maxLength(255),


            Forms\Components\Textarea::make('description')
                ->label('توضیحات')
                ->rows(4)
                ->columnSpanFull(),


            Forms\Components\Select::make('quizable_type')
                ->label('آزمون مربوط به')
                ->options([

                    Section::class => 'بخش',

                    Chapter::class => 'فصل',

                    Book::class => 'کتاب',

                ])
                ->live()
                ->required()
                ->afterStateUpdated(function ($state, Forms\Set $set) {

                    $set('quizable_id', null);

                }),


            Forms\Components\Select::make('quizable_id')
                ->label('انتخاب محتوا')
                ->options(function (Get $get) {

                    $type = $get('quizable_type');


                    if ($type === Section::class) {

                        return Section::query()
                            ->pluck('title', 'id');

                    }


                    if ($type === Chapter::class) {

                        return Chapter::query()
                            ->pluck('title', 'id');

                    }


                    if ($type === Book::class) {

                        return Book::query()
                            ->pluck('title', 'id');

                    }


                    return [];

                })
                ->searchable()
                ->preload()
                ->required(),



            Forms\Components\TextInput::make('questions_count')
                ->label('تعداد سوال')
                ->numeric()
                ->default(10)
                ->required(),


            Forms\Components\TextInput::make('time_limit')
                ->label('زمان آزمون (دقیقه)')
                ->numeric()
                ->default(20),


            Forms\Components\TextInput::make('passing_percentage')
                ->label('درصد قبولی')
                ->numeric()
                ->default(50)
                ->minValue(0)
                ->maxValue(100),


            Forms\Components\TextInput::make('max_attempts')
                ->label('حداکثر دفعات شرکت')
                ->numeric()
                ->default(1),


            Forms\Components\Toggle::make('randomize_questions')
                ->label('تصادفی کردن سوالات')
                ->default(true),


            Forms\Components\Toggle::make('randomize_options')
                ->label('تصادفی کردن گزینه‌ها')
                ->default(true),


            Forms\Components\Toggle::make('show_result')
                ->label('نمایش نتیجه')
                ->default(true),


            Forms\Components\Toggle::make('show_correct_answers')
                ->label('نمایش پاسخ صحیح')
                ->default(false),


            Forms\Components\Toggle::make('is_free')
                ->label('رایگان')
                ->default(false),


            Forms\Components\Select::make('status')
                ->label('وضعیت')
                ->options([

                    'draft' => 'پیش نویس',

                    'pending' => 'در انتظار بررسی',

                    'active' => 'فعال',

                    'inactive' => 'غیرفعال',

                ])
                ->default('draft')
                ->required(),


            Forms\Components\DateTimePicker::make('published_at')
                ->label('زمان انتشار'),

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


                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable(),


                Tables\Columns\TextColumn::make('creator.name')
                    ->label('سازنده'),


                Tables\Columns\TextColumn::make('questions_count')
                    ->label('تعداد سوال'),


                Tables\Columns\TextColumn::make('time_limit')
                    ->label('زمان'),


                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->colors([

                        'gray' => 'draft',

                        'warning' => 'pending',

                        'success' => 'active',

                        'danger' => 'inactive',

                    ]),


                Tables\Columns\IconColumn::make('is_free')
                    ->label('رایگان')
                    ->boolean(),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->dateTime('Y/m/d H:i'),

            ])


            ->filters([

                Tables\Filters\SelectFilter::make('status')
                    ->options([

                        'draft' => 'پیش نویس',

                        'pending' => 'در انتظار بررسی',

                        'active' => 'فعال',

                        'inactive' => 'غیرفعال',

                    ]),


                Tables\Filters\TernaryFilter::make('is_free')
                    ->label('رایگان'),


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

            QuestionsRelationManager::class,

        ];
    }


    public static function getPages(): array
    {
        return [

            'index' => Pages\ListQuizzes::route('/'),

            'create' => Pages\CreateQuiz::route('/create'),

            'edit' => Pages\EditQuiz::route('/{record}/edit'),

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
