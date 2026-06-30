<?php

namespace App\Livewire\Auth;

use App\Models\StaffDetails;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class ForgotPassword extends Component
{
    public string $contactNumber = '';
    public string $password = '';
    public string $passwordConfirmation = '';

    protected function rules(): array
    {
        return [
            'contactNumber' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'password' => 'new password',
        ];
    }

    public function updatePassword(): void
    {
        $this->validate([
            'contactNumber' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
            'passwordConfirmation' => ['required', 'same:password'],
        ]);

        // Find the staff member by contact number, then update the
        // linked user's password.
        $staff = StaffDetails::where('ContactNumber', $this->contactNumber)->first();

        if (! $staff || ! $staff->user) {
            $this->addError('contactNumber', 'No account found with that contact number.');
            return;
        }

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
