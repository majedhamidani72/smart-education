<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use App\Traits\FiltersByTeacherAssignment;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookResource extends Resource
{
    use FiltersByTeacherAssignment;


    protected static ?string $model = Book::class;


    protected static ?string $navigationIcon = 'heroicon-o-book-open';


    protected static ?string $navigationGroup = 'مدیریت آموزش';


    protected static ?string $navigationLabel = 'کتاب‌ها';


    protected static ?string $modelLabel = 'کتاب';


    protected static ?string $pluralModelLabel = 'کتاب‌ها';


    protected static ?int $navigationSort = 3;



    public static function form(Form $form): Form
    {
        return $form->schema([


            Select::make('grade_subject_id')
                ->label('پایه / درس')
                ->relationship(
                    name: 'gradeSubject',
                    titleAttribute: 'id'
                )
                ->getOptionLabelFromRecordUsing(
                    fn ($record) =>
                    $record->grade->title . ' - ' . $record->subject->title
                )
                ->searchable()
                ->preload()
                ->required(),



            TextInput::make('title')
                ->label('عنوان کتاب')
                ->required()
                ->maxLength(255),



            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),



            FileUpload::make('cover')
                ->label('جلد کتاب')
                ->directory('books')
                ->image(),



            TextInput::make('academic_year')
                ->label('سال تحصیلی')
                ->maxLength(20),



            TextInput::make('pages_count')
                ->label('تعداد صفحات')
                ->numeric(),



            Textarea::make('description')
                ->label('توضیحات')
                ->rows(4)
                ->columnSpanFull(),



            TextInput::make('sort_order')
                ->label('ترتیب نمایش')
                ->numeric()
                ->default(1)
                ->required(),



            Toggle::make('is_active')
                ->label('فعال')
                ->default(true),


        ]);
    }



    public static function table(Table $table): Table
    {
        return $table

            ->defaultSort('sort_order')

            ->columns([


                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),



                ImageColumn::make('cover')
                    ->label('جلد'),



                TextColumn::make('gradeSubject.grade.title')
                    ->label('پایه'),



                TextColumn::make('gradeSubject.subject.title')
                    ->label('درس'),



                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable(),



                TextColumn::make('academic_year')
                    ->label('سال'),



                TextColumn::make('pages_count')
                    ->label('صفحات'),



                IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),



                TextColumn::make('created_at')
                    ->label('ایجاد')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),


            ])



            ->filters([


                TernaryFilter::make('is_active')
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


            'index' => Pages\ListBooks::route('/'),


            'create' => Pages\CreateBook::route('/create'),


            'edit' => Pages\EditBook::route('/{record}/edit'),


        ];
    }



    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()

            ->withoutGlobalScopes([

                SoftDeletingScope::class,

            ]);


        return static::applyTeacherFilter($query);
    }
}
