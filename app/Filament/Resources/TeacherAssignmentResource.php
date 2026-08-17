<?php

namespace App\Filament\Resources;


use App\Filament\Resources\TeacherAssignmentResource\Pages;

use App\Models\TeacherAssignment;


use Filament\Forms;

use Filament\Forms\Form;


use Filament\Resources\Resource;


use Filament\Tables;

use Filament\Tables\Table;


use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\SoftDeletingScope;

use Illuminate\Validation\Rules\Unique;



class TeacherAssignmentResource extends Resource
{


    protected static ?string $model = TeacherAssignment::class;



    protected static ?string $navigationIcon =
    'heroicon-o-user-group';



    protected static ?string $navigationGroup =
    'مدیریت کاربران';



    protected static ?string $navigationLabel =
    'اختصاص معلمان';



    protected static ?string $modelLabel =
    'اختصاص معلم';



    protected static ?string $pluralModelLabel =
    'اختصاص معلمان';



    protected static ?int $navigationSort = 2;


    public static function shouldRegisterNavigation(): bool
    {
        // این Resource دیگر در منو نمایش داده نمی‌شود. اختصاص
        // کتاب به معلم حالا مستقیماً داخل فرم TeacherResource
        // (همان لحظه‌ی ساخت/ویرایش معلم) انجام می‌گیرد.
        return false;
    }


    public static function form(Form $form): Form
    {

        return $form->schema([



            Forms\Components\Select::make('teacher_id')

                ->label('معلم')

                ->relationship(

                    name: 'teacher',

                    titleAttribute: 'name',

                    modifyQueryUsing: fn(Builder $query) =>
                    $query->role('Teacher')

                )

                ->searchable()

                ->preload()

                ->required(),





            Forms\Components\Select::make('book_id')

                ->label('کتاب')

                ->relationship(

                    name: 'book',

                    titleAttribute: 'title'

                )

                ->getOptionLabelFromRecordUsing(

                    fn($record) =>

                    $record->appGradeSubject->grade->title

                        . ' - ' .

                        $record->appGradeSubject->subject->title

                        . ' - ' .

                        $record->title

                )

                ->searchable()

                ->preload()

                ->required()

                ->unique(

                    ignoreRecord: true,

                    modifyRuleUsing: fn(

                        Unique $rule,

                        callable $get

                    ) => $rule->where(

                        'teacher_id',

                        $get('teacher_id')

                    )

                ),





            Forms\Components\Toggle::make('is_active')

                ->label('فعال')

                ->default(true),


        ]);
    }





    public static function table(Table $table): Table
    {

        return $table

            ->defaultSort(

                'created_at',

                'desc'

            )

            ->columns([



                Tables\Columns\TextColumn::make('teacher.name')

                    ->label('معلم')

                    ->searchable()

                    ->sortable(),




                Tables\Columns\TextColumn::make(
                    'book.appGradeSubject.grade.title'
                )

                    ->label('پایه')

                    ->sortable(),




                Tables\Columns\TextColumn::make(
                    'book.appGradeSubject.subject.title'
                )

                    ->label('درس')

                    ->sortable(),




                Tables\Columns\TextColumn::make('book.title')

                    ->label('کتاب')

                    ->searchable()

                    ->sortable(),




                Tables\Columns\TextColumn::make(
                    'assignedBy.name'
                )

                    ->label('ثبت کننده'),




                Tables\Columns\IconColumn::make('is_active')

                    ->label('فعال')

                    ->boolean(),




                Tables\Columns\TextColumn::make('created_at')

                    ->label('تاریخ ایجاد')

                    ->dateTime('Y-m-d H:i'),



            ])





            ->filters([



                Tables\Filters\SelectFilter::make('teacher_id')

                    ->label('معلم')

                    ->relationship(

                        'teacher',

                        'name'

                    ),




                Tables\Filters\SelectFilter::make('book_id')

                    ->label('کتاب')

                    ->relationship(

                        'book',

                        'title'

                    ),




                Tables\Filters\TernaryFilter::make('is_active')

                    ->label('فعال'),




                Tables\Filters\TrashedFilter::make(),


            ])





            ->actions([


                Tables\Actions\EditAction::make(),


                Tables\Actions\DeleteAction::make(),


                Tables\Actions\RestoreAction::make(),



            ])





            ->bulkActions([



                Tables\Actions\BulkActionGroup::make([


                    Tables\Actions\DeleteBulkAction::make(),


                    Tables\Actions\RestoreBulkAction::make(),


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

            'index' => Pages\ListTeacherAssignments::route('/'),

            'create' => Pages\CreateTeacherAssignment::route('/create'),

            'edit' => Pages\EditTeacherAssignment::route('/{record}/edit'),

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
