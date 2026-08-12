<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Models\Video;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


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
        return $form
            ->schema([


                Forms\Components\Select::make('content_item_id')

                    ->label('محتوای آموزشی')

                    ->relationship(
                        'contentItem',
                        'title'
                    )

                    ->searchable()

                    ->preload()

                    ->required()
                    ,





                Forms\Components\FileUpload::make('video_file')

                    ->label('فایل ویدئو')

                    ->disk('local')

                    ->directory('livewire-tmp')

                    ->visibility('private')

                    ->acceptedFileTypes([

                        'video/mp4',

                        'video/webm',

                        'video/x-matroska',

                    ])

                    ->maxSize(512000)

                    ->required()

                    ->preserveFilenames(false)

                    ->storeFileNamesIn('original_name')

                    ->columnSpanFull(),





                Forms\Components\TextInput::make('quality')

                    ->label('کیفیت')

                    ->maxLength(30),





                Forms\Components\Toggle::make('download_allowed')

                    ->label('اجازه دانلود')

                    ->default(false),
                Forms\Components\Select::make('processing_status')

                    ->label('وضعیت پردازش')

                    ->options([

                        'pending' => 'در انتظار بررسی',

                        'processing' => 'در حال پردازش',

                        'approved' => 'تأیید شده',

                        'rejected' => 'رد شده',

                    ])

                    ->disabled()

                    ->dehydrated(false),





                Forms\Components\Textarea::make('rejected_reason')

                    ->label('دلیل رد')

                    ->rows(4)

                    ->columnSpanFull(),

            ]);
    }





    public static function table(Table $table): Table
    {
        return $table

            ->columns([



                Tables\Columns\TextColumn::make('id')

                    ->label('#')

                    ->sortable(),





                Tables\Columns\TextColumn::make('contentItem.title')

                    ->label('محتوا')

                    ->searchable()

                    ->sortable(),





                Tables\Columns\TextColumn::make('uploader.name')

                    ->label('آپلودکننده')

                    ->searchable(),





                Tables\Columns\TextColumn::make('original_name')

                    ->label('نام فایل')

                    ->limit(40)

                    ->searchable(),





                Tables\Columns\BadgeColumn::make('processing_status')

                    ->label('وضعیت')

                    ->colors([

                        'warning' => 'pending',

                        'info' => 'processing',

                        'success' => 'approved',

                        'danger' => 'rejected',

                    ]),





                Tables\Columns\TextColumn::make('file_size')

                    ->label('حجم')

                    ->formatStateUsing(

                        fn($state) => $state

                            ? round($state / 1024 / 1024, 2) . ' MB'

                            : '-'

                    ),





                Tables\Columns\TextColumn::make('duration')

                    ->label('مدت زمان')

                    ->formatStateUsing(

                        fn($state) => $state

                            ? gmdate('H:i:s', $state)

                            : '-'

                    ),





                Tables\Columns\TextColumn::make('created_at')

                    ->label('تاریخ')

                    ->dateTime(),

            ])





            ->filters([



                Tables\Filters\SelectFilter::make('processing_status')

                    ->label('وضعیت')

                    ->options([

                        'pending' => 'در انتظار بررسی',

                        'processing' => 'در حال پردازش',

                        'approved' => 'تأیید شده',

                        'rejected' => 'رد شده',

                    ]),

            ])





            ->actions([



                Tables\Actions\EditAction::make(),





                Tables\Actions\Action::make('approve')

                    ->label('تأیید')

                    ->color('success')

                    ->requiresConfirmation()

                    ->visible(

                        fn(Video $record) =>

                        $record->processing_status === 'pending'

                    )

                    ->action(

                        fn(Video $record) =>

                        app(\App\Services\VideoService::class)

                            ->approve($record)

                    ),





                Tables\Actions\Action::make('reject')

                    ->label('رد')

                    ->color('danger')

                    ->form([

                        Forms\Components\Textarea::make('reason')

                            ->label('دلیل رد')

                            ->required(),

                    ])

                    ->visible(

                        fn(Video $record) =>

                        $record->processing_status === 'pending'

                    )

                    ->action(

                        fn(Video $record, array $data) =>

                        app(\App\Services\VideoService::class)

                            ->reject(

                                $record,

                                $data['reason']

                            )

                    ),





                Tables\Actions\DeleteAction::make(),

            ])





            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\DeleteBulkAction::make(),

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

            'index'  => Pages\ListVideos::route('/'),

            'create' => Pages\CreateVideo::route('/create'),

            'edit'   => Pages\EditVideo::route('/{record}/edit'),

        ];
    }
}
