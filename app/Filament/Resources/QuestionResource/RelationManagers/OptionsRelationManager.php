<?php

namespace App\Filament\Resources\QuestionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;

use Filament\Resources\RelationManagers\RelationManager;

use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Model;


class OptionsRelationManager extends RelationManager
{

    protected static string $relationship = 'options';



    public function form(Form $form): Form
    {
        return $form->schema([


            Forms\Components\TextInput::make('option_text')

                ->label('متن گزینه')

                ->required()

                ->maxLength(255),




            Forms\Components\FileUpload::make('image_path')

                ->label('تصویر گزینه')

                ->image()

                ->directory('question-options'),




            Forms\Components\Toggle::make('is_correct')

                ->label('پاسخ صحیح')

                ->default(false)

                ->helperText(
                    'برای هر سوال فقط یک گزینه صحیح مجاز است.'
                ),




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


            ->recordTitleAttribute(
                'option_text'
            )


            ->defaultSort(
                'sort_order'
            )


            ->columns([


                Tables\Columns\TextColumn::make(
                    'option_text'
                )

                    ->label('گزینه')

                    ->searchable(),



                Tables\Columns\ImageColumn::make(
                    'image_path'
                )

                    ->label('تصویر'),



                Tables\Columns\IconColumn::make(
                    'is_correct'
                )

                    ->label('پاسخ صحیح')

                    ->boolean(),



                Tables\Columns\TextColumn::make(
                    'sort_order'
                )

                    ->label('ترتیب')

                    ->sortable(),



            ])



            ->headerActions([


                Tables\Actions\CreateAction::make()

                    ->mutateFormDataUsing(
                        function(array $data): array {

                            if(
                                $data['is_correct'] ?? false
                            ){

                                $this->ownerRecord
                                    ->options()
                                    ->update([
                                        'is_correct'=>false
                                    ]);

                            }


                            return $data;

                        }
                    ),


            ])




            ->actions([


                Tables\Actions\EditAction::make()

                    ->mutateFormDataUsing(
                        function(array $data): array {


                            if(
                                $data['is_correct'] ?? false
                            ){

                                $this->ownerRecord
                                    ->options()
                                    ->where(
                                        'id',
                                        '!=',
                                        $this->record?->id
                                    )
                                    ->update([
                                        'is_correct'=>false
                                    ]);

                            }


                            return $data;

                        }
                    ),



                Tables\Actions\DeleteAction::make(),


            ])




            ->bulkActions([


                Tables\Actions\BulkActionGroup::make([


                    Tables\Actions\DeleteBulkAction::make(),


                ]),


            ]);

    }

}
