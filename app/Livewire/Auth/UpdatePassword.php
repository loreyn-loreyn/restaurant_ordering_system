<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UpdatePassword extends Component
{
    public string $password = '';
    public string $passwordConfirmation = '';

    public function updatePassword(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'min:8'],
            'passwordConfirmation' => ['required', 'same:password'],
        ]);

        $user = Auth::user();
        $user->update([
            'Password' => Hash::make($this->password),
        ]);

        session()->flash('status', 'Password updated successfully.');

        $this->reset(['password', 'passwordConfirmation']);
    }

    public function render()
    {
        return view('livewire.auth.update-password');
    }
}
