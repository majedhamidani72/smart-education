<?php

namespace App\Filament\Pages;

use App\Models\TeacherProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * پروفایل من (مخصوص معلم)
 * --------------------------------------------------------------------
 * هر معلم فقط خودش می‌تواند اینجا را ببیند و ویرایش کند — عکس
 * پروفایل، سابقه‌ی خدمت، و شماره کارت برای تسویه‌حساب درآمدش.
 * شماره‌ی کارت همان‌جا در ستون‌های «درآمد معلمان» هم نمایش داده
 * می‌شود تا ادمین موقع واریز، لازم نباشد جای دیگری دنبالش بگردد.
 */
class MyTeacherProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'پروفایل من';

    protected static ?string $title = 'پروفایل من';

    // این صفحه بالای همه‌ی گروه‌ها (بدون گروه) نمایش داده می‌شود
    // تا همیشه اولین چیزی باشد که معلم می‌بیند.
    protected static ?string $navigationGroup = null;

    protected static ?int $navigationSort = -10;

    protected static string $view = 'filament.pages.my-teacher-profile';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Teacher');
    }

    public function mount(): void
    {
        $profile = auth()->user()->teacherProfile;

        $this->form->fill([

            'photo' => $profile?->photo,

            'years_of_experience' => $profile?->years_of_experience,

            'card_number' => $profile?->card_number,

        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\FileUpload::make('photo')
                    ->label('عکس پروفایل')
                    ->disk('public')
                    ->directory('teacher-profiles')
                    ->image()
                    ->avatar()
                    ->imageEditor()
                    ->circleCropper(),

                Forms\Components\TextInput::make('years_of_experience')
                    ->label('سابقه‌ی خدمت در آموزش‌وپرورش (سال)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(60),

                Forms\Components\TextInput::make('card_number')
                    ->label('شماره کارت (برای تسویه‌حساب درآمد)')
                    ->mask('9999-9999-9999-9999')
                    ->placeholder('xxxx-xxxx-xxxx-xxxx')
                    ->maxLength(19)
                    ->helperText('این شماره فقط برای واریز درآمدت به خودت استفاده می‌شود و در دسترس دانش‌آموزان نیست.'),

            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        TeacherProfile::updateOrCreate(

            ['user_id' => auth()->id()],

            $data

        );

        Notification::make()
            ->title('پروفایل با موفقیت ذخیره شد.')
            ->success()
            ->send();

        // آواتار بالای داشبورد جزو همین Livewire نیست و خودکار
        // به‌روز نمی‌شود؛ برای همین بعد از ذخیره، کل صفحه یک‌بار
        // رفرش می‌شود تا عکس جدید بالای صفحه هم فوری دیده شود.
        $this->js('window.location.reload()');
    }

    /**
     * اگر همین کاربر نقش ادمین هم دارد، لینک سریع برگشت به
     * پروفایل ادمینی‌اش.
     */
    public function getAdminProfileUrl(): ?string
    {
        if (! auth()->user()->hasRole('Admin')) {
            return null;
        }

        return \App\Filament\Pages\MyAdminProfile::getUrl();
    }
}
