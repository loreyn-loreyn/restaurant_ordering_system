<?php

namespace App\Livewire\Auth;

use App\Support\RoleRedirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')]
class UpdatePassword extends Component
{
    public string $password = '';
    public string $passwordConfirmation = '';
    public bool $showPassword = false;
    public bool $showPasswordConfirmation = false;

    // Set right before validation so the 'different' rule below can compare
    // against it; not rendered or exposed in the form.
    public string $currentDefaultPassword = '';

    protected function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                // Don't let them "update" to the same default password.
                'different:currentDefaultPassword',
            ],
            'passwordConfirmation' => ['required', 'same:password'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'password' => 'new password',
            'passwordConfirmation' => 'password confirmation',
        ];
    }

    protected function messages(): array
    {
        return [
            'password.min' => 'Password must be at least 8 characters long.',
            'password.mixed_case' => 'Password must contain both upper and lowercase letters.',
            'password.numbers' => 'Password must contain at least one number.',
            'password.symbols' => 'Password must contain at least one special character (e.g. !@#$%).',
            'password.different' => 'You cannot reuse your default (birthdate) password.',
        ];
    }

    public function togglePassword(): void
    {
        $this->showPassword = !$this->showPassword;
    }

    public function togglePasswordConfirmation(): void
    {
        $this->showPasswordConfirmation = !$this->showPasswordConfirmation;
    }

    public function updatePassword(): void
    {
        $user = Auth::user()->load('staffDetails', 'role');

        // Block re-setting the password back to the birthdate default.
        $this->currentDefaultPassword = $user->staffDetails?->BirthDate?->format('mdY') ?? '';

        $this->validate();

        $user->update([
            'Password' => Hash::make($this->password),
        ]);

        session()->flash('status', 'Password updated successfully.');

        // Send the user on to their role's landing page (Admin, Manager,
        // Cashier, Kitchen Staff, etc.) now that their password is set.
        $this->redirectRoute(RoleRedirect::routeFor($user->role?->RoleName), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.update-password');
    }
}