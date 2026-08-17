<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ChangePassword extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'change-password';

    protected static ?string $title = 'تغییر رمز عبور';

    protected static string $view = 'filament.pages.change-password';

    public ?array $data = [];

    public function mount(): void
    {
        if (! auth()->check()) {

            $this->redirectRoute(
                'filament.admin.auth.login'
            );

            return;
        }

        if (! auth()->user()->must_change_password) {

            $this->redirect(route('agreement.show'));

            return;
        }

        $this->form->fill();
    }

    public function form(
        Form $form
    ): Form {

        return $form

            ->schema([

                Forms\Components\TextInput::make('password')

                    ->label('رمز عبور جدید')

                    ->password()

                    ->revealable()

                    ->required()

                    ->rule(

                        Password::min(8)

                            ->letters()

                            ->numbers()

                    )

                    ->confirmed()

                    ->validationMessages([

                        'required' => 'رمز عبور الزامی است.',

                        'confirmed' => 'تکرار رمز عبور صحیح نیست.',

                    ]),

                Forms\Components\TextInput::make('password_confirmation')

                    ->label('تکرار رمز عبور')

                    ->password()

                    ->revealable()

                    ->required(),

            ])

            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = auth()->user();

        if (

            Hash::check(

                $data['password'],

                $user->password

            )

        ) {

            throw ValidationException::withMessages([

                'data.password' => 'رمز جدید نباید با رمز قبلی یکسان باشد.',

            ]);
        }

        $user->update([

            'password' => Hash::make(
                $data['password']
            ),

            'must_change_password' => false,

            'remember_token' => Str::random(60),

        ]);

        auth()->logoutOtherDevices(
            $data['password']
        );

        session()->invalidate();

        session()->regenerateToken();

        auth()->login($user);

        Notification::make()

            ->title('رمز عبور با موفقیت تغییر کرد.')

            ->success()

            ->send();

        $this->redirectRoute(
            'filament.admin.pages.dashboard'
        );
    }
}
