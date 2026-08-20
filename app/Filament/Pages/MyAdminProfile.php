<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * پروفایل من (مخصوص ادمین)
 * --------------------------------------------------------------------
 * فقط عکس پروفایل — ادمین برخلاف معلم، فیلد سابقه/شماره‌کارت
 * ندارد (چون این‌ها مخصوص چیزهایی است که مرتبط با تدریس/تسویه‌ی
 * معلم است).
 */
class MyAdminProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'پروفایل من';

    protected static ?string $title = 'پروفایل من';

    protected static ?string $navigationGroup = null;

    protected static ?int $navigationSort = -10;

    protected static string $view = 'filament.pages.my-admin-profile';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    public function mount(): void
    {
        $this->form->fill([
            'avatar' => auth()->user()->avatar,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\FileUpload::make('avatar')
                    ->label('عکس پروفایل')
                    ->disk('public')
                    ->directory('admin-profiles')
                    ->image()
                    ->avatar()
                    ->imageEditor()
                    ->circleCropper(),

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        auth()->user()->update($data);

        Notification::make()
            ->title('پروفایل با موفقیت ذخیره شد.')
            ->success()
            ->send();
    }
}
