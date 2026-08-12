<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers\OptionsRelationManager;
use App\Models\ContentItem;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'مدیریت آزمون';

    protected static ?string $navigationLabel = 'سوالات';

    protected static ?string $modelLabel = 'سوال';

    protected static ?string $pluralModelLabel = 'سوالات';


    public static function form(Form $form): Form
    {
        return $form->schema([


            Forms\Components\Select::make('content_item_id')
                ->label('محتوای آموزشی')
                ->relationship('contentItem', 'title')
                ->searchable()
                ->preload()
                ->required(),


            Forms\Components\Select::make('question_topic_id')
                ->label('موضوع سوال')
                ->relationship('topic', 'title')
                ->searchable()
                ->preload()
                ->required(),


            Forms\Components\Textarea::make('question_text')
                ->label('متن سوال')
                ->required()
                ->rows(5)
                ->columnSpanFull(),


            Forms\Components\FileUpload::make('image_path')
                ->label('تصویر سوال')
                ->image()
                ->directory('questions'),


            Forms\Components\Textarea::make('explanation')
                ->label('توضیح پاسخ صحیح')
                ->rows(4)
                ->columnSpanFull(),


            Forms\Components\FileUpload::make('explanation_image_path')
                ->label('تصویر توضیح پاسخ')
                ->image()
                ->directory('questions/explanations'),


            Forms\Components\Select::make('difficulty')
                ->label('سطح سختی')
                ->options([
                    'easy' => 'آسان',
                    'medium' => 'متوسط',
                    'hard' => 'سخت',
                ])
                ->default('easy')
                ->required(),


            Forms\Components\TextInput::make('default_score')
                ->label('امتیاز سوال')
                ->numeric()
                ->default(1)
                ->required(),


            Forms\Components\Select::make('status')
                ->label('وضعیت')
                ->options([
                    'draft' => 'پیش نویس',
                    'pending' => 'در انتظار بررسی',
                    'approved' => 'تایید شده',
                    'rejected' => 'رد شده',
                ])
                ->default('draft')
                ->required(),


            Forms\Components\Textarea::make('rejection_reason')
                ->label('دلیل رد'),


            Forms\Components\Toggle::make('is_active')
                ->label('فعال')
                ->default(true),

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


                Tables\Columns\TextColumn::make('contentItem.title')
                    ->label('محتوای آموزشی')
                    ->searchable(),


                Tables\Columns\TextColumn::make('topic.title')
                    ->label('موضوع')
                    ->searchable(),


                Tables\Columns\TextColumn::make('question_text')
                    ->label('سوال')
                    ->limit(50)
                    ->searchable(),


                Tables\Columns\TextColumn::make('difficulty')
                    ->label('سختی')
                    ->badge()
                    ->colors([
                        'success' => 'easy',
                        'warning' => 'medium',
                        'danger' => 'hard',
                    ]),


                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),


                Tables\Columns\TextColumn::make('creator.name')
                    ->label('سازنده'),


                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->dateTime('Y/m/d H:i'),

            ])


            ->filters([

                Tables\Filters\SelectFilter::make('difficulty')
                    ->label('سختی')
                    ->options([
                        'easy' => 'آسان',
                        'medium' => 'متوسط',
                        'hard' => 'سخت',
                    ]),


                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'draft' => 'پیش نویس',
                        'pending' => 'در انتظار بررسی',
                        'approved' => 'تایید شده',
                        'rejected' => 'رد شده',
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

                    Tables\Actions\RestoreBulkAction::make(),

                    Tables\Actions\ForceDeleteBulkAction::make(),

                ]),

            ]);
    }



    public static function getRelations(): array
    {
        return [

            OptionsRelationManager::class,

        ];
    }



    public static function getPages(): array
    {
        return [

            'index' => Pages\ListQuestions::route('/'),

            'create' => Pages\CreateQuestion::route('/create'),

            'edit' => Pages\EditQuestion::route('/{record}/edit'),

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
