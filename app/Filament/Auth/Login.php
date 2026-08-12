<?php

namespace App\Filament\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;

class Login extends BaseLogin
{
    /**
     * فیلد شماره موبایل
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('mobile')
            ->label('شماره موبایل')
            ->tel()
            ->required()
            ->autofocus()
            ->autocomplete('tel')
            ->maxLength(11)
            ->minLength(11);
    }

    /**
     * اعتبارسنجی ورود
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [

            'mobile' => $data['mobile'],

            'password' => $data['password'],

        ];
    }

    /**
     * پیام خطا
     */
    protected function throwFailureValidationException(): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([

            'data.mobile' => 'شماره موبایل یا رمز عبور اشتباه است.',

        ]);
    }
}
