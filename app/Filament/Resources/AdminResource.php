<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * مدیریت ادمین‌ها
 * --------------------------------------------------------------------
 * برخلاف UserResource قدیمی (که یک فرم عمومی با انتخاب نقش از
 * دراپ‌داون بود)، این Resource مخصوص «ساخت ادمین»‌ست: هیچ فیلد
 * انتخاب نقشی در فرم وجود ندارد، چون نقش «Admin» به‌صورت خودکار
 * و ثابت در لحظه‌ی ایجاد به کاربر داده می‌شود (نگاه کنید به
 * Pages\CreateAdmin::afterCreate).
 *
 * قانون دسترسی: طبق تصمیم پروژه، ادمین به تمام امکاناتی که
 * معلم دارد هم دسترسی خواهد داشت. این قانون در Policyها و
 * Middlewareهای مربوط به هر Resource دیگر اعمال می‌شود، نه اینجا؛
 * این فایل فقط مسئول فرم «ایجاد/ویرایش ادمین» است.
 *
 * محدودیت امنیتی مهم: فقط SuperAdmin اجازه دارد ادمین بسازد یا
 * ویرایش کند (نگاه کنید به shouldRegisterNavigation). یک Admin
 * معمولی نباید بتواند ادمین جدید بسازد یا سطح دسترسی ادمین
 * دیگری را تغییر دهد.
 */
class AdminResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'مدیریت کاربران';

    protected static ?string $navigationLabel = 'ادمین';

    protected static ?string $modelLabel = 'ادمین';

    protected static ?string $pluralModelLabel = 'ادمین‌ها';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        // فقط سوپرادمین اجازه‌ی مدیریت ادمین‌ها را دارد.
        return auth()->user()?->hasRole('SuperAdmin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('name')
                ->label('نام ادمین')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('mobile')
                ->label('شماره موبایل')
                ->required()
                ->tel()
                ->maxLength(11)
                ->unique(
                    table: User::class,
                    column: 'mobile',
                    ignoreRecord: true,
                    modifyRuleUsing: fn($rule) => $rule->whereNull('deleted_at'),
                ),

            Forms\Components\TextInput::make('password')
                ->label('رمز اولیه')
                ->password()
                ->revealable()
                ->required(fn(string $operation) => $operation === 'create')
                ->dehydrated(fn($state) => filled($state))
                ->helperText('در حالت ویرایش، اگر خالی بماند رمز قبلی تغییر نمی‌کند.'),

            Forms\Components\Toggle::make('must_change_password')
                ->label('اجبار تغییر رمز در اولین ورود')
                ->default(true),

            Forms\Components\Toggle::make('is_active')
                ->label('فعال')
                ->default(true),

            Forms\Components\Placeholder::make('access_note')
                ->label('سطح دسترسی')
                ->content(
                    'ادمین به تمام امکانات مدیریت آموزشی و تمام '
                        . 'امکاناتی که معلم دارد دسترسی خواهد داشت.'
                ),

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

                Tables\Columns\TextColumn::make('name')
                    ->label('نام')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('موبایل')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->dateTime('Y/m/d H:i'),

            ])
            ->filters([

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
        return [];
    }

    public static function getPages(): array
    {
        return [

            'index' => Pages\ListAdmins::route('/'),

            'create' => Pages\CreateAdmin::route('/create'),

            'edit' => Pages\EditAdmin::route('/{record}/edit'),

        ];
    }

    /**
     * فقط کاربرانی که نقش Admin دارند در این Resource دیده می‌شوند.
     * معلمان، سوپرادمین و دانش‌آموزان اینجا لیست نمی‌شوند.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->role('Admin');
    }
}
