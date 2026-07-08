<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StaffDetails;
use App\Models\User;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\UpdatePassword;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, InteractsWithSession;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        // Livewire::test() doesn't run the HTTP kernel, so StartSession
        // middleware never binds a session store onto the shared Request
        // singleton. Login::login() calls request()->session()->regenerate(),
        // which throws "Session store not set on request" without this.
        $this->startSession();
    }

    // ── Login ────────────────────────────────────────────────────────

    public function test_unknown_username_shows_error(): void
    {
        Livewire::test(Login::class)
            ->set('username', 'NOSUCHUSER')
            ->set('password', 'whatever')
            ->call('login')
            ->assertHasErrors('username');

        $this->assertGuest();
    }

    public function test_pending_approval_account_cannot_login(): void
    {
        // K007 seeded with AccountApprovalStatus = 0
        Livewire::test(Login::class)
            ->set('username', 'K007')
            ->set('password', '12251999')
            ->call('login')
            ->assertHasErrors('username');

        $this->assertGuest();
    }

    public function test_deactivated_account_cannot_login(): void
    {
        // C005 seeded with AccountStatus = 0
        Livewire::test(Login::class)
            ->set('username', 'C005')
            ->set('password', '07141998')
            ->call('login')
            ->assertHasErrors('username');

        $this->assertGuest();
    }

    public function test_wrong_password_shows_error(): void
    {
        Livewire::test(Login::class)
            ->set('username', 'C002')
            ->set('password', 'not-the-right-password')
            ->call('login')
            ->assertHasErrors('password');

        $this->assertGuest();
    }

    /**
     * Every seeded account's password is still its birthdate default, so
     * logging in with the seeded credentials must always redirect to the
     * forced password-update screen rather than the role's landing page.
     */
    public function test_successful_login_with_default_password_forces_update_password(): void
    {
        Livewire::test(Login::class)
            ->set('username', 'C002')
            ->set('password', '03212001')
            ->call('login')
            ->assertRedirect(route('password.update'));

        $this->assertAuthenticated();
    }

    public function test_successful_login_creates_a_login_log_row(): void
    {
        $user = User::where('UserName', 'C002')->firstOrFail();

        Livewire::test(Login::class)
            ->set('username', 'C002')
            ->set('password', '03212001')
            ->call('login');

        $this->assertDatabaseHas('login_logs', [
            'UserID' => $user->UserID,
            'LogoutAt' => null,
        ]);
    }

    /**
     * Once an account's password is no longer the birthdate default,
     * login should send the user straight to their role's landing page.
     */
    public function test_successful_login_after_password_changed_redirects_to_role_landing(): void
    {
        $cashierRole = Role::where('RoleName', 'Cashier')->firstOrFail();

        $user = User::create([
            'RoleID' => $cashierRole->RoleID,
            'UserName' => 'C099',
            'Password' => Hash::make('N3wStrongPass!'),
            'DateIssued' => now()->toDateString(),
            'AccountStatus' => true,
            'AccountApprovalStatus' => true,
        ]);

        StaffDetails::create([
            'StaffID' => 'C099',
            'UserID' => $user->UserID,
            'RoleID' => $cashierRole->RoleID,
            'LastName' => 'Test', 'FirstName' => 'Cashier', 'MiddleName' => null,
            'Age' => 30, 'BirthDate' => '1995-01-01', 'Sex' => 'M',
            'BirthPlace' => 'Manila', 'Nationality' => 'Filipino',
            'Address' => 'Somewhere', 'ContactNumber' => '09991234567',
            'Email' => 'c099@example.com', 'HiredDate' => now()->toDateString(),
        ]);

        Livewire::test(Login::class)
            ->set('username', 'C099')
            ->set('password', 'N3wStrongPass!')
            ->call('login')
            ->assertRedirect(route('cashier.order-type'));
    }

    // ── UpdatePassword ───────────────────────────────────────────────

    public function test_update_password_rejects_the_birthdate_default(): void
    {
        $user = User::where('UserName', 'C002')->firstOrFail();
        $this->actingAs($user);

        Livewire::test(UpdatePassword::class)
            ->set('password', '03212001') // same as the birthdate default
            ->set('passwordConfirmation', '03212001')
            ->call('updatePassword')
            ->assertHasErrors('password');
    }

    public function test_update_password_rejects_weak_password(): void
    {
        $user = User::where('UserName', 'C002')->firstOrFail();
        $this->actingAs($user);

        Livewire::test(UpdatePassword::class)
            ->set('password', 'weakpass') // no upper/number/symbol
            ->set('passwordConfirmation', 'weakpass')
            ->call('updatePassword')
            ->assertHasErrors('password');
    }

    public function test_update_password_rejects_mismatched_confirmation(): void
    {
        $user = User::where('UserName', 'C002')->firstOrFail();
        $this->actingAs($user);

        Livewire::test(UpdatePassword::class)
            ->set('password', 'Str0ng!Pass')
            ->set('passwordConfirmation', 'Different!1')
            ->call('updatePassword')
            ->assertHasErrors('passwordConfirmation');
    }

    public function test_update_password_success_redirects_to_role_landing(): void
    {
        $user = User::where('UserName', 'C002')->firstOrFail();
        $this->actingAs($user);

        Livewire::test(UpdatePassword::class)
            ->set('password', 'Str0ng!Pass')
            ->set('passwordConfirmation', 'Str0ng!Pass')
            ->call('updatePassword')
            ->assertRedirect(route('cashier.order-type'));

        $this->assertTrue(Hash::check('Str0ng!Pass', $user->fresh()->Password));
    }

    // ── ForgotPassword ───────────────────────────────────────────────

    public function test_forgot_password_unknown_contact_number_shows_error(): void
    {
        Livewire::test(ForgotPassword::class)
            ->set('contactNumber', '00000000000')
            ->set('password', 'Str0ng!Pass')
            ->set('passwordConfirmation', 'Str0ng!Pass')
            ->call('updatePassword')
            ->assertHasErrors('contactNumber');
    }

    public function test_forgot_password_rejects_reusing_birthdate_default(): void
    {
        // Maria Santos / C002, ContactNumber 09191234567, birthdate 2001-03-21
        Livewire::test(ForgotPassword::class)
            ->set('contactNumber', '09191234567')
            ->set('password', '03212001')
            ->set('passwordConfirmation', '03212001')
            ->call('updatePassword')
            ->assertHasErrors('password');
    }

    public function test_forgot_password_success_updates_password_and_redirects_to_login(): void
    {
        $user = User::where('UserName', 'C002')->firstOrFail();

        Livewire::test(ForgotPassword::class)
            ->set('contactNumber', '09191234567')
            ->set('password', 'Str0ng!Pass')
            ->set('passwordConfirmation', 'Str0ng!Pass')
            ->call('updatePassword')
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('Str0ng!Pass', $user->fresh()->Password));
    }
}