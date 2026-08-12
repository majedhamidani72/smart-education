<?php

namespace App\Filament\Resources\QuizResource\RelationManagers;

use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';


    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('score')
                ->label('امتیاز سوال')
                ->numeric()
                ->default(1)
                ->required(),


            Forms\Components\TextInput::make('sort_order')
                ->label('ترتیب نمایش')
                ->numeric()
                ->default(1)
                ->required(),

        ]);
    }



    public function table(Table $table): Table
    {
        return $table

            ->recordTitleAttribute('question_text')

            ->columns([


                Tables\Columns\TextColumn::make('question_text')
                    ->label('سوال')
                    ->limit(60)
                    ->searchable(),


                Tables\Columns\TextColumn::make('contentItem.title')
                    ->label('محتوای آموزشی')
                    ->searchable(),


                Tables\Columns\TextColumn::make('difficulty')
                    ->label('سختی')
                    ->badge()
                    ->colors([
                        'success' => 'easy',
                        'warning' => 'medium',
                        'danger' => 'hard',
                    ]),


                Tables\Columns\TextColumn::make('pivot.score')
                    ->label('امتیاز'),


                Tables\Columns\TextColumn::make('pivot.sort_order')
                    ->label('ترتیب'),

            ])



            ->headerActions([


                Tables\Actions\AttachAction::make()
                    ->label('افزودن سوال')

                    ->preloadRecordSelect()

                    ->recordSelectOptionsQuery(function ($query) {

                        return $query
                            ->where('status', 'approved')
                            ->where('is_active', true);

                    })

                    ->recordSelectSearchColumns([
                        'question_text',
                    ])

                    ->form(fn (Forms\Form $form) => $form->schema([


                        Forms\Components\Select::make('recordId')
                            ->label('انتخاب سوال')

                            ->options(function () {

                                return Question::query()
                                    ->where('status', 'approved')
                                    ->where('is_active', true)
                                    ->pluck('question_text', 'id');

                            })

                            ->searchable()
                            ->required(),



                        Forms\Components\TextInput::make('score')
                            ->label('امتیاز')
                            ->numeric()
                            ->default(1)
                            ->required(),



                        Forms\Components\TextInput::make('sort_order')
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
