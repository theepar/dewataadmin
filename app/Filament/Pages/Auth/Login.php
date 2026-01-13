<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable
    {
        return 'Dewata Property Admin';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Silakan masuk untuk melanjutkan';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/login.form.password.label'))
            ->hint(
                filament()->hasPasswordReset()
                    ? new \Illuminate\Support\HtmlString('<a href="' . filament()->getRequestPasswordResetUrl() . '">' . __('filament-panels::pages/auth/login.form.password_reset_url.label') . '</a>')
                    : null
            )
            ->password()
            ->revealable() // Fitur show/hide password
            ->required()
            ->autocomplete('current-password')
            ->extraInputAttributes(['tabindex' => 2]);
    }
}
