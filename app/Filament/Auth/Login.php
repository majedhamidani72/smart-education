<?php

namespace App\Filament\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
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
            ->maxLength(11)
            ->minLength(11);
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
            'data.mobile' =>
                'شماره موبایل یا رمز عبور اشتباه است.',
        ]);
    }


    public function authenticate(): ?LoginResponse
    {
        $this->rateLimit(5);


        $data = $this->form->getState();


        if (
            ! Auth::attempt(
                $this->getCredentialsFromFormData($data)
            )
        ) {

            $this->throwFailureValidationException();

        }


        $user = Auth::user();


        session()->regenerate();


        if (
            $user->must_change_password
        ) {

            session()->put(
                'must_change_password_user',
                $user->id
            );

        }


        return app(LoginResponse::class);
    }
}
