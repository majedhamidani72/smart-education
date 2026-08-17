<?php

declare(strict_types=1);

namespace App\Filament\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('mobile')
            ->label('شماره موبایل')
            ->tel()
            ->required()
            ->autofocus()
            ->autocomplete('tel')
            ->placeholder('09123456789')
            ->rule('digits:11')
            ->rule('regex:/^09[0-9]{9}$/')
            ->validationMessages([
                'required' => 'شماره موبایل الزامی است.',
                'digits' => 'شماره موبایل باید ۱۱ رقم باشد.',
                'regex' => 'فرمت شماره موبایل صحیح نیست.',
            ]);
    }

    protected function getCredentialsFromFormData(
        array $data
    ): array {
        return [
            'mobile' => $data['mobile'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.mobile' => 'شماره موبایل یا رمز عبور اشتباه است.',
        ]);
    }

    public function authenticate(): ?LoginResponse
    {
        $this->rateLimit(5);

        $data = $this->form->getState();

        if (! Auth::attempt(
            $this->getCredentialsFromFormData($data)
        )) {
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | ادامه فرآیند ورود
        |--------------------------------------------------------------------------
        | تصمیم برای اجبار تغییر رمز عبور داخل Middleware
        | CheckPasswordChange گرفته می‌شود.
        */

        return app(LoginResponse::class);
    }

    protected function getPasswordResetUrl(): ?string
    {
        return null;
    }
}
