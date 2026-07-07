<?php

namespace App\Livewire\Auth;

use App\Models\StaffDetails;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class ForgotPassword extends Component
{
    public string $contactNumber = '';
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
            'contactNumber' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
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
        // Find the staff member by contact number first, since the
        // birthdate-default check below needs their linked account.
        $staff = StaffDetails::where('ContactNumber', $this->contactNumber)->first();

        if (! $staff || ! $staff->user) {
            $this->addError('contactNumber', 'No account found with that contact number.');
            return;
        }

        $this->currentDefaultPassword = $staff->BirthDate?->format('mdY') ?? '';

        $this->validate();

        $staff->user->update([
            'Password' => Hash::make($this->password),
        ]);

        session()->flash('status', 'Password updated. You can now log in.');

        $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}