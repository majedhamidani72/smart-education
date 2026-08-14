<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class ChangePassword extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static string $view = 'filament.pages.change-password';

    protected static ?string $title = 'تغییر رمز عبور';

    public ?array $data = [];


    public function mount(): void
    {
        if (! auth()->check()) {

            redirect('/admin/login');

            return;
        }


        $user = auth()->user();


        if (! $user->must_change_password) {

            redirect('/admin');

            return;
        }


        $this->form->fill();
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('password')
                    ->label('رمز جدید')
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->same('password_confirmation'),


                Forms\Components\TextInput::make('password_confirmation')
                    ->label('تکرار رمز جدید')
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


        $user->update([

            'password' => Hash::make(
                $data['password']
            ),

            'must_change_password' => false,

        ]);


        session()->forget(
            'must_change_password_user'
        );


        session()->regenerate();


        Notification::make()
            ->title('رمز عبور با موفقیت تغییر کرد.')
            ->success()
            ->send();


        redirect('/admin');
    }
}
