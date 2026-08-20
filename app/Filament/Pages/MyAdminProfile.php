<?php

namespace App\Filament\Pages;

use App\Models\TeacherProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * پروفایل من (مخصوص ادمین)
 * --------------------------------------------------------------------
 * عکس پروفایل + شماره کارت. شماره کارت را روی همان جدول
 * teacher_profiles ذخیره می‌کند — چون طبق تصمیم پروژه، ادمین
 * معمولاً همان معلم است (یک نفر، دو نقش)، پس شماره کارتش هم
 * باید یکی باشد، صرف‌نظر از این‌که با کدام نقش وارد پنل شده.
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
        $user = auth()->user();

        $this->form->fill([

            'avatar' => $user->avatar,

            'card_number' => $user->teacherProfile?->card_number,

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

                Forms\Components\TextInput::make('card_number')
                    ->label('شماره کارت (برای تسویه‌حساب درآمد ادمینی)')
                    ->mask('9999-9999-9999-9999')
                    ->placeholder('xxxx-xxxx-xxxx-xxxx')
                    ->maxLength(19)
                    ->helperText('اگر همزمان نقش معلم هم داری، همین شماره برای تسویه‌ی درآمد معلمی‌ات هم استفاده می‌شود.'),

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = auth()->user();

        $user->update([
            'avatar' => $data['avatar'],
        ]);

        TeacherProfile::updateOrCreate(

            ['user_id' => $user->id],

            ['card_number' => $data['card_number']]

        );

        Notification::make()
            ->title('پروفایل با موفقیت ذخیره شد.')
            ->success()
            ->send();

        // آواتار بالای داشبورد جزو همین Livewire نیست؛ برای دیدن
        // فوری عکس جدید، صفحه یک‌بار رفرش می‌شود.
        $this->js('window.location.reload()');
    }

    /**
     * اگر همین کاربر نقش معلم هم دارد (که طبق تصمیم پروژه حالت
     * رایجی است)، یک لینک سریع برای رفتن به صفحه‌ی مدیریت
     * کتاب‌های تدریسی خودش (بدون نیاز به خروج کامل از سیستم).
     */
    public function getTeacherEditUrl(): ?string
    {
        $user = auth()->user();

        if (! $user->hasRole('Teacher')) {
            return null;
        }

        return \App\Filament\Resources\TeacherResource::getUrl('edit', ['record' => $user]);
    }
}
