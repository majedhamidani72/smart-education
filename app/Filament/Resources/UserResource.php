<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'مدیریت کاربران';

    protected static ?string $navigationLabel = 'کاربران';

    protected static ?string $modelLabel = 'کاربر';

    protected static ?string $pluralModelLabel = 'کاربران';

    protected static ?int $navigationSort = 1;


    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()
            &&
            (
                auth()->user()->hasRole('SuperAdmin')
                ||
                auth()->user()->hasRole('Admin')
            );
    }


    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('name')
                ->label('نام')
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
                    modifyRuleUsing: function ($rule) {
                        return $rule->whereNull('deleted_at');
                    }
                ),


            Forms\Components\TextInput::make('password')
                ->label('رمز اولیه')
                ->password()
                ->revealable()
                ->dehydrated(fn($state) => filled($state)),


            Forms\Components\Toggle::make('must_change_password')
                ->label('اجبار تغییر رمز')
                ->default(false),


            Forms\Components\Select::make('roles')
                ->label('نقش کاربر')
                ->options(
                    Role::query()
                        ->pluck('name', 'name')
                )
                ->searchable()
                ->required(),


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

                Tables\Columns\TextColumn::make('name')
                    ->label('نام')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('موبایل')
                    ->searchable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('نقش')
                    ->badge(),

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

            'index' => Pages\ListUsers::route('/'),

            'create' => Pages\CreateUser::route('/create'),

            'edit' => Pages\EditUser::route('/{record}/edit'),

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
