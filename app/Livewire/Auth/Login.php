<?php

namespace App\Livewire\Auth;

use App\Models\LoginLog;
use App\Models\User;
use App\Support\RoleRedirect;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    public string $username = '';
    public string $password = '';
    public bool $showPassword = false;

    protected function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function togglePassword(): void
    {
        $this->showPassword = !$this->showPassword;
    }

    public function login(): void
    {
        $this->validate();

        // Step 1: check the username actually exists in the database.
        $user = User::where('UserName', $this->username)->first();

        if (! $user) {
            $this->addError('username', 'This username does not exist.');
            return;
        }

        // Step 2: check the account has been approved by an admin/manager.
        if (! $user->AccountApprovalStatus) {
            $this->addError('username', 'Your account is still pending approval.');
            return;
        }

        // Step 3: check the account is active (not disabled).
        if (! $user->AccountStatus) {
            $this->addError('username', 'Your account has been deactivated. Contact your administrator.');
            return;
        }

        // Step 4: verify the password and log in.
        if (! Auth::attempt(['UserName' => $this->username, 'password' => $this->password])) {
            $this->addError('password', 'Incorrect password.');
            return;
        }

        // Use the session() helper (resolves from the container) rather than
        // request()->session(): Livewire::test() invokes component methods
        // through its own internal request broker, which builds a fresh
        // Request instance that never passes through StartSession
        // middleware. request()->session() would throw "Session store not
        // set on request" in that context. session() always resolves the
        // same underlying store, in both real HTTP requests and tests.
        session()->regenerate();

        // Step 5: figure out the role of the now-authenticated user and
        // redirect them to that role's landing page.
        $authUser = Auth::user()->load('role', 'staffDetails');
        $roleName = $authUser->role?->RoleName;

        // Record the login for the Admin "User Logs" view. LogoutAt stays
        // null until the /logout route (or an Admin deactivating the
        // account) closes it out.
        LoginLog::create([
            'UserID' => $authUser->UserID,
            'LoginAt' => now(),
        ]);

        // Step 6: if they're still logging in with their default (birthdate)
        // password, force them to update-password before letting them
        // reach their role's landing page.
        $defaultPassword = $authUser->staffDetails?->BirthDate?->format('mdY');

        if ($defaultPassword !== null && $this->password === $defaultPassword) {
            $this->redirectRoute('password.update', navigate: true);
            return;
        }

        $this->redirectRoute(RoleRedirect::routeFor($roleName), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}