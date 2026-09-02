<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PowerpointResource\Pages;
use App\Models\App;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Grade;
use App\Models\Powerpoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PowerpointResource extends Resource
{
    protected static ?string $model = Powerpoint::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationGroup = 'فروشگاه مستقل';
    protected static ?string $navigationLabel = 'پاورپوینت‌های تدریس';
    protected static ?string $modelLabel = 'پاورپوینت';
    protected static ?string $pluralModelLabel = 'پاورپوینت‌های تدریس';

    public static function shouldRegisterNavigation(): bool { return auth()->user()?->hasRole('SuperAdmin') ?? false; }
    public static function canViewAny(): bool { return auth()->user()?->hasRole('SuperAdmin') ?? false; }
    public static function canCreate(): bool { return auth()->user()?->hasRole('SuperAdmin') ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->hasRole('SuperAdmin') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasRole('SuperAdmin') ?? false; }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('مسیر آموزشی')->columns(4)->schema([
                Forms\Components\Select::make('app_id')->label('اپلیکیشن')->options(App::where('is_active', true)->orderBy('sort_order')->pluck('title', 'id'))->live()->required()->afterStateUpdated(fn (Set $set) => $set('grade_id', null)),
                Forms\Components\Select::make('grade_id')->label('پایه')->options(fn (Get $get) => Grade::whereHas('appGradeSubjects', fn ($q) => $q->where('app_id', $get('app_id')))->orderBy('grade_number')->pluck('title', 'id'))->live()->required()->afterStateUpdated(fn (Set $set) => $set('book_id', null)),
                Forms\Components\Select::make('book_id')->label('کتاب')->options(fn (Get $get) => Book::whereHas('appGradeSubject', fn ($q) => $q->where('app_id', $get('app_id'))->where('grade_id', $get('grade_id')))->where('is_active', true)->pluck('title', 'id'))->live()->required()->afterStateUpdated(fn (Set $set) => $set('chapter_id', null)),
                Forms\Components\Select::make('chapter_id')->label('فصل')->options(fn (Get $get) => Chapter::where('book_id', $get('book_id'))->where('is_active', true)->orderBy('sort_order')->pluck('title', 'id'))->required(),
            ]),
            Forms\Components\TextInput::make('title')->label('عنوان فروش')->placeholder('پاورپوینت آماده تدریس فصل عددنویسی')->required()->maxLength(255)->live(onBlur: true)->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state).'-'.Str::lower(Str::random(5))))->columnSpanFull(),
            Forms\Components\Hidden::make('slug'),
            Forms\Components\Textarea::make('description')->label('توضیحات')->rows(3)->columnSpanFull(),
            Forms\Components\FileUpload::make('file_path')->label('فایل پاورپوینت')->disk('local')->directory('powerpoints')->acceptedFileTypes(['application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation'])->maxSize(51200)->downloadable()->required()->columnSpanFull(),
            Forms\Components\FileUpload::make('preview_image')->label('تصویر پیش‌نمایش')->disk('public')->directory('powerpoint-previews')->image()->imageEditor()->columnSpanFull(),
            Forms\Components\FileUpload::make('preview_pdf_path')
                ->label('PDF نمونه برای ورق‌زدن')
                ->helperText('چند اسلاید منتخب را به PDF تبدیل و اینجا بارگذاری کنید؛ فایل اصلی پاورپوینت نمایش داده نمی‌شود.')
                ->disk('local')->directory('powerpoint-samples')->acceptedFileTypes(['application/pdf'])
                ->maxSize(20480)->columnSpanFull(),
            Forms\Components\TextInput::make('price')
                ->label('قیمت اصلی (تومان)')
                ->numeric()->minValue(0)->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, $state) {
                    // اگر قیمت اصلی صفر شد (یعنی محصول رایگان شد)، قیمت با
                    // تخفیف را هم صفر می‌کنیم — چون منطقاً تخفیف روی محصول
                    // رایگان معنا ندارد و اگر مقدار قدیمی‌اش (مثلاً از وقتی
                    // که پولی بود) باقی بماند، قانون «باید کمتر از قیمت
                    // اصلی باشد» رد می‌شود و کل فرم ذخیره نمی‌شود — بدون
                    // این‌که همیشه دلیلش برای کاربر واضح باشد.
                    if ((int) $state === 0) {
                        $set('discount_price', null);
                    }
                }),
            Forms\Components\TextInput::make('discount_price')
                ->label('قیمت با تخفیف (تومان)')
                ->numeric()->minValue(0)->lt('price')
                ->disabled(fn (Get $get) => (int) $get('price') === 0)
                ->dehydrated(fn (Get $get) => (int) $get('price') !== 0)
                ->helperText('اختیاری؛ باید کمتر از قیمت اصلی باشد. برای محصول رایگان (قیمت اصلی صفر) غیرفعال می‌شود.'),
            Forms\Components\TextInput::make('slides_count')->label('تعداد اسلاید')->numeric()->minValue(1),
            Forms\Components\TextInput::make('sample_slides_count')->label('تعداد اسلاید نمونه')->numeric()->minValue(1),
            Forms\Components\TagsInput::make('features')->label('ویژگی‌ها')->placeholder('مثلاً کاملاً قابل ویرایش')->helperText('هر ویژگی را نوشته و Enter بزنید.')->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')->label('ترتیب')->numeric()->default(1)->required(),
            Forms\Components\Toggle::make('is_active')->label('فعال برای فروش')->default(true),
            Forms\Components\Toggle::make('is_featured')->label('نمایش در ویترین منتخب')->default(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([
            Tables\Columns\ImageColumn::make('preview_image')->label('پیش‌نمایش')->disk('public'),
            Tables\Columns\TextColumn::make('title')->label('عنوان')->searchable()->wrap(),
            Tables\Columns\TextColumn::make('grade.title')->label('پایه')->badge(),
            Tables\Columns\TextColumn::make('book.title')->label('کتاب'),
            Tables\Columns\TextColumn::make('chapter.title')->label('فصل'),
            Tables\Columns\TextColumn::make('price')->label('قیمت')->formatStateUsing(fn ($state) => number_format($state) . ' تومان'),
            Tables\Columns\TextColumn::make('discount_price')->label('قیمت فروش')->formatStateUsing(fn ($state) => $state === null ? null : number_format($state) . ' تومان')->placeholder('بدون تخفیف'),
            Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
            Tables\Columns\IconColumn::make('is_featured')->label('منتخب')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()->label('ویرایش'), Tables\Actions\DeleteAction::make()->label('حذف')->requiresConfirmation()])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPowerpoints::route('/'), 'create' => Pages\CreatePowerpoint::route('/create'), 'edit' => Pages\EditPowerpoint::route('/{record}/edit')];
    }
}
