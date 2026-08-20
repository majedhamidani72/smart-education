<?php

namespace App\Filament\Resources\QuizResource\RelationManagers;


use App\Models\Question;

use Filament\Forms;
use Filament\Forms\Form;

use Filament\Resources\RelationManagers\RelationManager;

use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;



class QuestionsRelationManager extends RelationManager
{

    protected static string $relationship = 'questions';



    public function form(Form $form): Form
    {
        return $form->schema([


            Forms\Components\TextInput::make('score')

                ->label('امتیاز سوال')

                ->numeric()

                ->minValue(0)

                ->default(1)

                ->required(),



            Forms\Components\TextInput::make('sort_order')

                ->label('ترتیب نمایش')

                ->numeric()

                ->minValue(1)

                ->default(1)

                ->required(),


        ]);
    }





    /**
     * دریافت کتاب مربوط به آزمون
     */
    protected function getQuizBookId(): ?int
    {

        $quiz = $this->ownerRecord;


        if (
            $quiz->quizable_type === \App\Models\Book::class
        ) {

            return $quiz->quizable_id;

        }



        if (
            $quiz->quizable_type === \App\Models\Chapter::class
        ) {

            return $quiz
                ->quizable
                ->book_id;

        }



        if (
            $quiz->quizable_type === \App\Models\Section::class
        ) {

            return $quiz
                ->quizable
                ->chapter
                ->book_id;

        }



        return null;

    }





    /**
     * سوالات مجاز برای این آزمون
     */
    protected function questionQuery(): Builder
    {

        $query = Question::query()

            ->where('status','approved')

            ->where('is_active',true);



        $bookId = $this->getQuizBookId();



        if($bookId){


            $query->whereHas(

                'contentItem.section.chapter.book',

                function($builder) use ($bookId){


                    $builder->where(
                        'books.id',
                        $bookId
                    );


                }

            );


        }



        return $query;

    }






    public function table(Table $table): Table
    {

        return $table


            ->recordTitleAttribute(
                'question_text'
            )


            ->columns([



                Tables\Columns\TextColumn::make(
                    'question_text'
                )

                    ->label('سوال')

                    ->limit(60)

                    ->searchable(),




                Tables\Columns\TextColumn::make(
                    'contentItem.title'
                )

                    ->label('محتوا'),




                Tables\Columns\TextColumn::make(
                    'difficulty'
                )

                    ->label('سختی')

                    ->badge()

                    ->colors([

                        'success'=>'easy',

                        'warning'=>'medium',

                        'danger'=>'hard',

                    ]),




                Tables\Columns\TextColumn::make(
                    'pivot.score'
                )

                    ->label('امتیاز'),




                Tables\Columns\TextColumn::make(
                    'pivot.sort_order'
                )

                    ->label('ترتیب'),



            ])




            ->headerActions([



                Tables\Actions\AttachAction::make()

                    ->label('افزودن سوال')



                    ->recordSelectOptionsQuery(

                        fn (Builder $query) =>

                        $this->questionQuery()

                    )



                    ->preloadRecordSelect()



                    ->recordSelectSearchColumns([

                        'question_text',

                    ])




                    ->form(fn(Form $form)=>$form->schema([



                        Forms\Components\Select::make(
                            'recordId'
                        )

                            ->label('انتخاب سوال')

                            ->options(

                                fn()=> $this
                                    ->questionQuery()
                                    ->pluck(
                                        'question_text',
                                        'id'
                                    )

                            )

                            ->searchable()

                            ->required(),




                        Forms\Components\TextInput::make(
                            'score'
                        )

                            ->label('امتیاز')

                            ->numeric()

                            ->default(1)

                            ->required(),




                        Forms\Components\TextInput::make(
                            'sort_order'
                        )

                            ->label('ترتیب')

                            ->numeric()

                            ->default(1)

                            ->required(),



                    ])),


            ])




            ->actions([


                Tables\Actions\EditAction::make(),


                Tables\Actions\DetachAction::make(),


            ])




            ->bulkActions([


                Tables\Actions\BulkActionGroup::make([


                    Tables\Actions\DetachBulkAction::make(),


                ]),


            ]);

    }

}
