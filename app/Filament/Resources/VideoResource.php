<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Section;
use App\Models\Video;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationGroup = 'مدیریت آموزش';

    protected static ?string $navigationLabel = 'ویدئوها';

    protected static ?string $modelLabel = 'ویدئو';

    protected static ?string $pluralModelLabel = 'ویدئوها';



    public static function form(Form $form): Form
    {
        return $form->schema([


            Forms\Components\Select::make('book_id')
                ->label('کتاب')
                ->options(function () {

                    $user = Auth::user();


                    if (
                        $user &&
                        $user->hasRole('Teacher')
                    ) {

                        return Book::whereHas(
                            'teacherAssignments',
                            fn($query) =>
                            $query
                                ->where(
                                    'teacher_id',
                                    $user->id
                                )
                                ->where(
                                    'is_active',
                                    true
                                )
                        )
                            ->where(
                                'is_active',
                                true
                            )
                            ->pluck(
                                'title',
                                'id'
                            );
                    }


                    return Book::where(
                        'is_active',
                        true
                    )
                        ->pluck(
                            'title',
                            'id'
                        );
                })
                ->searchable()
                ->preload()
                ->live()
                ->required(),




            Forms\Components\Select::make('chapter_id')
                ->label('فصل')
                ->options(function (Forms\Get $get) {

                    $bookId = $get('book_id');


                    if (! $bookId) {

                        return [];
                    }


                    return Chapter::where(
                        'book_id',
                        $bookId
                    )
                        ->where(
                            'is_active',
                            true
                        )
                        ->orderBy(
                            'sort_order'
                        )
                        ->pluck(
                            'title',
                            'id'
                        );
                })
                ->searchable()
                ->live()
                ->required()

                ->createOptionForm([

                    Forms\Components\TextInput::make('title')
                        ->label('عنوان فصل')
                        ->required(),

                ])

                ->createOptionUsing(function (array $data, Forms\Get $get) {

                    return Chapter::create([

                        'book_id' =>
                        $get('book_id'),

                        'title' =>
                        $data['title'],

                        'slug' =>
                        Str::slug(
                            $data['title']
                        ),

                        'is_active' =>
                        true,

                        'sort_order' =>
                        1,

                    ])->id;
                }),




            Forms\Components\Select::make('section_id')
                ->label('بخش')
                ->options(function (Forms\Get $get) {

                    $chapterId = $get('chapter_id');


                    if (! $chapterId) {

                        return [];
                    }


                    return Section::where(
                        'chapter_id',
                        $chapterId
                    )
                        ->where(
                            'is_active',
                            true
                        )
                        ->orderBy(
                            'sort_order'
                        )
                        ->pluck(
                            'title',
                            'id'
                        );
                })
                ->searchable()
                ->required()

                ->createOptionForm([

                    Forms\Components\TextInput::make('title')
                        ->label('عنوان بخش')
                        ->required(),

                ])

                ->createOptionUsing(function (array $data, Forms\Get $get) {

                    return Section::create([

                        'chapter_id' =>
                        $get('chapter_id'),

                        'title' =>
                        $data['title'],

                        'slug' =>
                        Str::slug(
                            $data['title']
                        ),

                        'is_active' =>
                        true,

                        'sort_order' =>
                        1,

                    ])->id;
                }),




            Forms\Components\TextInput::make('title')
                ->label('عنوان محتوا')
                ->required()
                ->maxLength(255),



            Forms\Components\TextInput::make('page_number')
                ->label('شماره صفحه')
                ->numeric(),
            Forms\Components\FileUpload::make('video_file')
                ->label('فایل ویدئو')

                ->disk('public')

                ->directory('uploads/videos')

                ->visibility('public')

                ->acceptedFileTypes([

                    'video/mp4',

                    'video/webm',

                    'video/x-matroska',

                ])

                ->maxSize(204800)

                ->required(
                    fn(string $operation) =>
                    $operation === 'create'
                )

                ->preserveFilenames(false)

                ->storeFileNamesIn('original_name')

                ->dehydrated(
                    fn($state) =>
                    filled($state)
                )

                ->columnSpanFull(),




            Forms\Components\Toggle::make('download_allowed')
                ->label('اجازه دانلود')
                ->default(false),




            Forms\Components\Select::make('processing_status')
                ->label('وضعیت پردازش')
                ->options([

                    'pending' =>
                    'در انتظار بررسی',

                    'processing' =>
                    'در حال پردازش',

                    'approved' =>
                    'تایید شده',

                    'rejected' =>
                    'رد شده',

                ])
                ->default('pending'),




            Forms\Components\Textarea::make('rejected_reason')
                ->label('دلیل رد شدن')
                ->rows(4)
                ->columnSpanFull(),

        ]);
    }






    public static function mutateFormDataBeforeFill(
        array $data
    ): array {


        $video = Video::with([

            'contentItem.section.chapter.book',

        ])
            ->find(
                request()->route('record')
            );



        if (! $video) {

            return $data;
        }



        $contentItem = $video->contentItem;



        if ($contentItem) {


            $data['title'] =
                $contentItem->title;



            $data['page_number'] =
                $contentItem->page_number;



            if ($contentItem->section) {


                $data['section_id'] =
                    $contentItem->section_id;



                $data['chapter_id'] =
                    $contentItem
                    ->section
                    ->chapter_id;



                $data['book_id'] =
                    $contentItem
                    ->section
                    ->chapter
                    ->book_id;
            }
        }




        $data['download_allowed'] =
            $video->download_allowed;



        $data['processing_status'] =
            $video->processing_status;



        $data['rejected_reason'] =
            $video->rejected_reason;



        if (

            $video->directory

            &&

            $video->filename

        ) {


            $data['video_file'] = [

                $video->directory
                    .
                    '/'
                    .
                    $video->filename,

            ];
        }




        return $data;
    }






    public static function table(
        Table $table
    ): Table {

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
                    'contentItem.section.chapter.book.title'
                )
                    ->label('کتاب')
                    ->searchable(),



                Tables\Columns\TextColumn::make(
                    'contentItem.section.chapter.title'
                )
                    ->label('فصل')
                    ->searchable(),



                Tables\Columns\TextColumn::make(
                    'contentItem.section.title'
                )
                    ->label('بخش')
                    ->searchable(),



                Tables\Columns\TextColumn::make(
                    'contentItem.title'
                )
                    ->label('عنوان')
                    ->searchable()
                    ->sortable(),



                Tables\Columns\TextColumn::make(
                    'contentItem.page_number'
                )
                    ->label('صفحه'),



                Tables\Columns\TextColumn::make(
                    'uploader.name'
                )
                    ->label('آپلود کننده'),



                Tables\Columns\BadgeColumn::make(
                    'processing_status'
                )
                    ->label('وضعیت')
                    ->colors([

                        'warning' =>
                        'pending',

                        'info' =>
                        'processing',

                        'success' =>
                        'approved',

                        'danger' =>
                        'rejected',

                    ]),




                Tables\Columns\TextColumn::make(
                    'file_size'
                )
                    ->label('حجم')
                    ->formatStateUsing(
                        fn($state) =>
                        $state
                            ? round(
                                $state / 1024 / 1024,
                                2
                            )
                            . ' MB'
                            : '-'
                    ),




                Tables\Columns\TextColumn::make(
                    'duration'
                )
                    ->label('مدت زمان')
                    ->formatStateUsing(
                        fn($state) =>
                        $state
                            ? gmdate(
                                'H:i:s',
                                $state
                            )
                            : '-'
                    ),

            ])

            ->actions([

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),

            ]);
    }






    public static function getRelations(): array
    {
        return [];
    }






    public static function getPages(): array
    {

        return [

            'index' =>
            Pages\ListVideos::route('/'),

            'create' =>
            Pages\CreateVideo::route('/create'),

            'edit' =>
            Pages\EditVideo::route('/{record}/edit'),

        ];
    }
}
