<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentItemResource\Pages;
use App\Models\ContentItem;
use App\Traits\FiltersByTeacherAssignment;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;

use Filament\Resources\Resource;

use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class ContentItemResource extends Resource
{

    use FiltersByTeacherAssignment;


    protected static ?string $model = ContentItem::class;


    protected static ?string $navigationIcon = 'heroicon-o-document-text';


    protected static ?string $navigationGroup = 'مدیریت آموزش';


    protected static ?string $navigationLabel = 'محتواهای آموزشی';


    protected static ?string $modelLabel = 'محتوا';


    protected static ?string $pluralModelLabel = 'محتواهای آموزشی';


    protected static ?int $navigationSort = 6;



    public static function form(Form $form): Form
    {
        return $form->schema([


            Forms\Components\Select::make('section_id')

                ->label('بخش')

                ->relationship(
                    'section',
                    'title'
                )

                ->searchable()

                ->preload()

                ->required(),



            Forms\Components\Select::make('content_type_id')

                ->label('نوع محتوا')

                ->relationship(
                    'contentType',
                    'title'
                )

                ->searchable()

                ->preload()

                ->required(),



            Forms\Components\TextInput::make('title')

                ->label('عنوان')

                ->required()

                ->maxLength(255),



            Forms\Components\TextInput::make('slug')

                ->label('Slug')

                ->required()

                ->unique(ignoreRecord: true)

                ->maxLength(255),



            Forms\Components\Textarea::make('description')

                ->label('توضیحات')

                ->rows(4)

                ->columnSpanFull(),



            Forms\Components\TextInput::make('page_number')

                ->label('شماره صفحه')

                ->numeric(),



            Forms\Components\FileUpload::make('thumbnail')

                ->label('تصویر شاخص')

                ->directory('content-items')

                ->image(),



            Forms\Components\Toggle::make('is_free')

                ->label('رایگان')

                ->default(false),



            Forms\Components\Select::make('status')

                ->label('وضعیت')

                ->required()

                ->default('draft')

                ->live()

                ->options([

                    'draft' => 'پیش نویس',

                    'pending' => 'در انتظار بررسی',

                    'approved' => 'تأیید شده',

                    'rejected' => 'رد شده',

                    'published' => 'منتشر شده',

                ]),



            Forms\Components\Textarea::make('rejection_reason')

                ->label('دلیل رد')

                ->rows(3)

                ->visible(
                    fn(Get $get) =>
                    $get('status') === 'rejected'
                )

                ->columnSpanFull(),



            Forms\Components\DateTimePicker::make('published_at')

                ->label('زمان انتشار'),



            Forms\Components\TextInput::make('sort_order')

                ->label('ترتیب نمایش')

                ->numeric()

                ->default(1)

                ->required(),


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



                Tables\Columns\ImageColumn::make('thumbnail')

                    ->label('تصویر'),



                Tables\Columns\TextColumn::make('section.title')

                    ->label('بخش')

                    ->searchable(),



                Tables\Columns\TextColumn::make('contentType.title')

                    ->label('نوع محتوا'),



                Tables\Columns\TextColumn::make('title')

                    ->label('عنوان')

                    ->searchable()

                    ->sortable(),



                Tables\Columns\BadgeColumn::make('status')

                    ->label('وضعیت')

                    ->colors([

                        'gray' => 'draft',

                        'warning' => 'pending',

                        'success' => [

                            'approved',

                            'published'

                        ],

                        'danger' => 'rejected',

                    ]),



                Tables\Columns\IconColumn::make('is_free')

                    ->label('رایگان')

                    ->boolean(),



                Tables\Columns\TextColumn::make('sort_order')

                    ->label('ترتیب')

                    ->sortable(),



                Tables\Columns\TextColumn::make('creator.name')

                    ->label('ایجاد کننده'),



                Tables\Columns\TextColumn::make('reviewer.name')

                    ->label('بررسی کننده'),



                Tables\Columns\TextColumn::make('created_at')

                    ->label('ایجاد')

                    ->dateTime('Y/m/d H:i')

                    ->sortable(),


            ])



            ->filters([


                Tables\Filters\SelectFilter::make('status')

                    ->options([

                        'draft' => 'پیش نویس',

                        'pending' => 'در انتظار بررسی',

                        'approved' => 'تأیید شده',

                        'rejected' => 'رد شده',

                        'published' => 'منتشر شده',

                    ]),



                Tables\Filters\TernaryFilter::make('is_free')

                    ->label('رایگان'),



                Tables\Filters\TrashedFilter::make(),


            ])



            ->actions([


                Tables\Actions\ViewAction::make(),


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

            'index' => Pages\ListContentItems::route('/'),

            'create' => Pages\CreateContentItem::route('/create'),

            'edit' => Pages\EditContentItem::route('/{record}/edit'),

        ];
    }




    public static function getEloquentQuery(): Builder
    {

        $query = parent::getEloquentQuery()

            ->withoutGlobalScopes([

                SoftDeletingScope::class,

            ]);



        return static::applyTeacherFilter(

            $query,

            'section.chapter.book.teacherAssignments'

        );

    }

}
