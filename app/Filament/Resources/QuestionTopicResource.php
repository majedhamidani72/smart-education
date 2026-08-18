<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionTopicResource\Pages;
use App\Models\QuestionTopic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionTopicResource extends Resource
{
    protected static ?string $model = QuestionTopic::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'آزمون آنلاین';

    protected static ?string $navigationLabel = 'موضوعات سوال';

    /**
     * این Resource دیگر در منوی بیرونی نمایش داده نمی‌شود. موضوع
     * سوال از داخل فرم «بانک سوالات» (QuestionResource) هم انتخاب
     * می‌شود هم ایجاد — دقیقاً همان الگویی که برای پایه/درس/کتاب/
     * فصل/بخش هم استفاده شده.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $modelLabel = 'موضوع سوال';

    protected static ?string $pluralModelLabel = 'موضوعات سوال';

    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('title')
                ->label('عنوان موضوع')
                ->required()
                ->maxLength(255),


            Forms\Components\Textarea::make('description')
                ->label('توضیحات')
                ->rows(4)
                ->columnSpanFull(),

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


                Tables\Columns\TextColumn::make('questions_count')
                    ->label('تعداد سوال')
                    ->counts('questions'),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime('Y/m/d H:i'),

            ])

            ->filters([

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

            'index' => Pages\ListQuestionTopics::route('/'),

            'create' => Pages\CreateQuestionTopic::route('/create'),

            'edit' => Pages\EditQuestionTopic::route('/{record}/edit'),

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
