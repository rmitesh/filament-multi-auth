<?php

namespace App\Livewire;

use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class EmployeeLogin extends SimplePage implements HasForms
{
    use InteractsWithForms, InteractsWithFormActions;

    /**
     * @var view-string
     */
    protected static string $view = 'livewire.employee-login';

    public ?array $data = [];

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();
    }

    public function getTitle(): string | Htmlable
    {
        return __('filament-panels::pages/auth/login.title');
    }

    public function getHeading(): string | Htmlable
    {
        return __('filament-panels::pages/auth/login.heading');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->autocomplete()
                    ->autofocus()
                    ->required()
                    ->extraInputAttributes(['tabindex' => 1]),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->autocomplete('current-password')
                    ->extraInputAttributes(['tabindex' => 2]),

                Forms\Components\Checkbox::make('remember')
                    ->label(__('filament-panels::pages/auth/login.form.remember.label'))
            ])
            ->statePath('data')
            ->model(Employee::class);
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-panels::pages/auth/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function authenticate()
    {
        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();
        if (
            ($user instanceof Employee && $user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.username' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}