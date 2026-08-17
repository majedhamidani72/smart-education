<?php

namespace App\Filament\Resources;


use App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource\RelationManagers\QuestionsRelationManager;


use App\Models\Book;
use App\Models\Chapter;
use App\Models\Section;
use App\Models\Quiz;


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



    protected static ?string $navigationIcon =
    'heroicon-o-clipboard-document-check';



    protected static ?string $navigationGroup =
    'آزمون آنلاین';



    protected static ?string $navigationLabel =
    'آزمون‌ها';



    protected static ?string $modelLabel =
    'آزمون';



    protected static ?string $pluralModelLabel =
    'آزمون‌ها';



    protected static ?int $navigationSort = 1;




    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */


    public static function form(Form $form): Form
    {

        return $form->schema([



            Forms\Components\TextInput::make('title')

                ->label('عنوان آزمون')

                ->required()

                ->maxLength(255),




            Forms\Components\Textarea::make('description')

                ->label('توضیحات آزمون')

                ->rows(4)

                ->columnSpanFull(),




            /*
            |--------------------------------------------------------------------------
            | نوع آزمون
            |--------------------------------------------------------------------------
            */


            Forms\Components\Select::make('quizable_type')


                ->label('سطح آزمون')


                ->options([


                    Book::class =>
                    'آزمون کتاب',


                    Chapter::class =>
                    'آزمون فصل',


                    Section::class =>
                    'آزمون بخش',


                ])


                ->live()


                ->required()


                ->afterStateUpdated(

                    function ($state, Forms\Set $set) {


                        $set(
                            'quizable_id',
                            null
                        );
                    }

                ),





            /*
            |--------------------------------------------------------------------------
            | انتخاب کتاب / فصل / بخش
            |--------------------------------------------------------------------------
            */


            Forms\Components\Select::make('quizable_id')


                ->label('انتخاب مورد آزمون')


                ->options(function (Get $get) {



                    $type = $get(
                        'quizable_type'
                    );



                    $user = auth()->user();




                    if (! $user) {


                        return [];
                    }





                    /*
                    |--------------------------------------------------------------------------
                    | Admin
                    |--------------------------------------------------------------------------
                    */


                    if (

                        $user->hasRole('SuperAdmin')

                        ||

                        $user->hasRole('Admin')

                    ) {


                        return match ($type) {



                            Book::class =>

                            Book::query()

                                ->where(
                                    'is_active',
                                    true
                                )

                                ->pluck(
                                    'title',
                                    'id'
                                ),




                            Chapter::class =>

                            Chapter::query()

                                ->where(
                                    'is_active',
                                    true
                                )

                                ->pluck(
                                    'title',
                                    'id'
                                ),




                            Section::class =>

                            Section::query()

                                ->where(
                                    'is_active',
                                    true
                                )

                                ->pluck(
                                    'title',
                                    'id'
                                ),




                            default => [],
                        };
                    }





                    /*
                    |--------------------------------------------------------------------------
                    | Teacher
                    |--------------------------------------------------------------------------
                    */


                    return match ($type) {



                        Book::class =>


                        Book::query()


                            ->whereHas(

                                'teacherAssignments',

                                function (Builder $query) use ($user) {


                                    $query

                                        ->where(
                                            'teacher_id',
                                            $user->id
                                        )

                                        ->where(
                                            'is_active',
                                            true
                                        );
                                }

                            )


                            ->where(
                                'is_active',
                                true
                            )


                            ->pluck(
                                'title',
                                'id'
                            ),






                        Chapter::class =>


                        Chapter::query()


                            ->whereHas(

                                'book.teacherAssignments',

                                function (Builder $query) use ($user) {


                                    $query

                                        ->where(
                                            'teacher_id',
                                            $user->id
                                        )

                                        ->where(
                                            'is_active',
                                            true
                                        );
                                }

                            )


                            ->where(
                                'is_active',
                                true
                            )


                            ->pluck(
                                'title',
                                'id'
                            ),






                        Section::class =>


                        Section::query()


                            ->whereHas(

                                'chapter.book.teacherAssignments',

                                function (Builder $query) use ($user) {


                                    $query

                                        ->where(
                                            'teacher_id',
                                            $user->id
                                        )

                                        ->where(
                                            'is_active',
                                            true
                                        );
                                }

                            )


                            ->where(
                                'is_active',
                                true
                            )


                            ->pluck(
                                'title',
                                'id'
                            ),






                        default => [],
                    };
                })


                ->searchable()


                ->preload()


                ->required(),





            /*
            |--------------------------------------------------------------------------
            | تنظیمات آزمون
            |--------------------------------------------------------------------------
            */


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
    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */


    public static function table(Table $table): Table
    {

        return $table


            ->defaultSort(
                'created_at',
                'desc'
            )


            ->columns([



                Tables\Columns\TextColumn::make('id')

                    ->label('#')

                    ->sortable(),




                Tables\Columns\TextColumn::make(
                    'quizable.gradeSubject.grade.title'
                )

                    ->label('پایه')

                    ->searchable(),




                Tables\Columns\TextColumn::make(
                    'quizable.gradeSubject.subject.title'
                )

                    ->label('درس')

                    ->searchable(),




                Tables\Columns\TextColumn::make(
                    'quizable.title'
                )

                    ->label('کتاب / فصل / بخش')

                    ->searchable(),




                Tables\Columns\TextColumn::make('title')

                    ->label('عنوان آزمون')

                    ->searchable()

                    ->sortable(),




                Tables\Columns\TextColumn::make(
                    'creator.name'
                )

                    ->label('سازنده'),




                Tables\Columns\TextColumn::make(
                    'questions_count'
                )

                    ->label('تعداد سوال'),




                Tables\Columns\TextColumn::make(
                    'time_limit'
                )

                    ->label('زمان (دقیقه)'),




                Tables\Columns\BadgeColumn::make(
                    'status'
                )

                    ->label('وضعیت')

                    ->colors([


                        'gray' => 'draft',

                        'warning' => 'pending',

                        'success' => 'active',

                        'danger' => 'inactive',


                    ]),




                Tables\Columns\IconColumn::make(
                    'is_free'
                )

                    ->label('رایگان')

                    ->boolean(),




                Tables\Columns\TextColumn::make(
                    'created_at'
                )

                    ->label('ایجاد')

                    ->dateTime('Y/m/d H:i'),



            ])




            ->filters([



                Tables\Filters\SelectFilter::make(
                    'status'
                )

                    ->label('وضعیت')

                    ->options([


                        'draft' => 'پیش نویس',

                        'pending' => 'در انتظار بررسی',

                        'active' => 'فعال',

                        'inactive' => 'غیرفعال',


                    ]),




                Tables\Filters\TernaryFilter::make(
                    'is_free'
                )

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





    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */


    public static function getRelations(): array
    {

        return [

            QuestionsRelationManager::class,

        ];
    }





    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    */


    public static function getPages(): array
    {

        return [


            'index' => Pages\ListQuizzes::route('/'),


            'create' => Pages\CreateQuiz::route('/create'),


            'edit' => Pages\EditQuiz::route('/{record}/edit'),


        ];
    }





    /*
    |--------------------------------------------------------------------------
    | Query Permission
    |--------------------------------------------------------------------------
    */


    public static function getEloquentQuery(): Builder
    {

        $query = parent::getEloquentQuery()


            ->withoutGlobalScopes([

                SoftDeletingScope::class,

            ]);



        $user = auth()->user();



        if (! $user) {


            return $query->whereRaw(
                '1 = 0'
            );
        }




        /*
        |--------------------------------------------------------------------------
        | SuperAdmin / Admin
        |--------------------------------------------------------------------------
        */


        if (

            $user->hasRole('SuperAdmin')

            ||

            $user->hasRole('Admin')

        ) {


            return $query;
        }





        /*
        |--------------------------------------------------------------------------
        | Teacher
        |--------------------------------------------------------------------------
        */


        if (
            $user->hasRole('Teacher')
        ) {



            return $query->whereHas(


                'quizable',


                function ($builder) use ($user) {



                    $builder->whereHas(


                        'teacherAssignments',


                        function ($assignment) use ($user) {



                            $assignment

                                ->where(
                                    'teacher_id',
                                    $user->id
                                )


                                ->where(
                                    'is_active',
                                    true
                                );
                        }


                    );
                }


            );
        }




        return $query->whereRaw(
            '1 = 0'
        );
    }
}
