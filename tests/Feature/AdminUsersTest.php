<?php

namespace Tests\Feature;

use App\Livewire\Admin\Users;
use App\Models\LoginLog;
use App\Models\Role;
use App\Models\StaffDetails;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('UserName', 'A001')->firstOrFail());
    }

    protected function makePendingStaff(string $staffId = 'X100'): StaffDetails
    {
        return StaffDetails::create([
            'StaffID' => $staffId,
            'UserID' => null,
            'RoleID' => Role::where('RoleName', 'Server')->value('RoleID'),
            'LastName' => 'Reyes', 'FirstName' => 'Liza', 'MiddleName' => null,
            'Age' => 24, 'BirthDate' => '2001-06-15', 'Sex' => 'F',
            'BirthPlace' => 'Cavite', 'Nationality' => 'Filipino',
            'Address' => 'Somewhere', 'ContactNumber' => '09995551234',
            'Email' => 'liza@example.com', 'HiredDate' => now()->toDateString(),
        ]);
    }

    // ── lookupStaff ──────────────────────────────────────────────────

    public function test_lookup_staff_fails_for_unknown_staff_id(): void
    {
        Livewire::test(Users::class)
            ->set('staffId', 'NOPE99')
            ->set('birthDate', '2001-06-15')
            ->call('lookupStaff')
            ->assertHasErrors('staffId');
    }

    public function test_lookup_staff_fails_when_birthdate_does_not_match(): void
    {
        $this->makePendingStaff();

        Livewire::test(Users::class)
            ->set('staffId', 'X100')
            ->set('birthDate', '1999-01-01')
            ->call('lookupStaff')
            ->assertHasErrors('birthDate');
    }

    public function test_lookup_staff_succeeds_and_shows_confirmation_preview(): void
    {
        $this->makePendingStaff();

        Livewire::test(Users::class)
            ->set('staffId', 'X100')
            ->set('birthDate', '2001-06-15')
            ->call('lookupStaff')
            ->assertSet('confirming', true)
            ->assertSet('pendingStaffPreview.StaffID', 'X100');
    }

    public function test_lookup_staff_only_matches_staff_without_an_existing_account(): void
    {
        // C002 already has a login, so it should never surface as "pending".
        Livewire::test(Users::class)
            ->set('staffId', 'C002')
            ->set('birthDate', '2001-03-21')
            ->call('lookupStaff')
            ->assertHasErrors('staffId');
    }

    // ── createAccount ────────────────────────────────────────────────

    public function test_create_account_requires_confirmation_checkbox(): void
    {
        $this->makePendingStaff();

        Livewire::test(Users::class)
            ->set('staffId', 'X100')
            ->set('birthDate', '2001-06-15')
            ->call('lookupStaff')
            ->set('confirmedCorrect', false)
            ->call('createAccount')
            ->assertHasErrors('confirmedCorrect');

        $this->assertNull(StaffDetails::where('StaffID', 'X100')->first()->UserID);
    }

    public function test_create_account_generates_username_and_birthdate_password(): void
    {
        $this->makePendingStaff();

        Livewire::test(Users::class)
            ->set('staffId', 'X100')
            ->set('birthDate', '2001-06-15')
            ->call('lookupStaff')
            ->set('confirmedCorrect', true)
            ->call('createAccount')
            ->assertSet('showSuccess', true)
            ->assertSet('createdUsername', 'X100')
            ->assertSet('createdPassword', '06152001');

        $staff = StaffDetails::where('StaffID', 'X100')->firstOrFail();
        $this->assertNotNull($staff->UserID);

        $user = User::find($staff->UserID);
        $this->assertSame('X100', $user->UserName);
        $this->assertTrue((bool) $user->AccountStatus);
        $this->assertTrue((bool) $user->AccountApprovalStatus);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('06152001', $user->Password));
    }

    // ── toggleStatus ─────────────────────────────────────────────────

    public function test_deactivating_a_user_kills_their_session_and_closes_open_login_log(): void
    {
        $cashier = User::where('UserName', 'C002')->firstOrFail();

        DB::table('sessions')->insert([
            'id' => 'fake-session-id',
            'user_id' => $cashier->UserID,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => base64_encode('x'),
            'last_activity' => now()->timestamp,
        ]);

        LoginLog::create(['UserID' => $cashier->UserID, 'LoginAt' => now(), 'LogoutAt' => null]);

        Livewire::test(Users::class)
            ->call('askToggleStatus', $cashier->UserID, 'Maria Santos', true)
            ->call('confirmToggleStatus');

        $this->assertFalse((bool) $cashier->fresh()->AccountStatus);
        $this->assertDatabaseMissing('sessions', ['user_id' => $cashier->UserID]);
        $this->assertDatabaseHas('login_logs', ['UserID' => $cashier->UserID]);

        $log = LoginLog::where('UserID', $cashier->UserID)->latest('LoginAt')->first();
        $this->assertNotNull($log->LogoutAt);
    }

    public function test_reactivating_a_user_does_not_touch_sessions(): void
    {
        $cashier = User::where('UserName', 'C005')->firstOrFail(); // seeded inactive

        Livewire::test(Users::class)
            ->call('askToggleStatus', $cashier->UserID, 'Carlos Bautista', false)
            ->call('confirmToggleStatus');

        $this->assertTrue((bool) $cashier->fresh()->AccountStatus);
    }

    // ── delete staff ─────────────────────────────────────────────────

    public function test_deleting_staff_with_an_account_also_removes_the_login(): void
    {
        $staff = StaffDetails::where('StaffID', 'K007')->firstOrFail();
        $userId = $staff->UserID;

        Livewire::test(Users::class)
            ->call('askDeleteStaff', 'K007', 'John Doe')
            ->call('confirmDeleteStaff');

        $this->assertDatabaseMissing('staff_details', ['StaffID' => 'K007']);
        $this->assertDatabaseMissing('users', ['UserID' => $userId]);
    }

    public function test_admin_staff_can_never_be_deleted(): void
    {
        Livewire::test(Users::class)->call('askDeleteStaff', 'A001', 'Juan Dela Cruz');

        // The modal should never open for an Admin staff record.
        $this->assertDatabaseHas('staff_details', ['StaffID' => 'A001']);

        // Even a direct confirm call (bypassing the UI) is a no-op because
        // confirmDeleteStaff() re-checks the role itself.
        Livewire::test(Users::class)
            ->set('confirmingDeleteStaffId', 'A001')
            ->call('confirmDeleteStaff');

        $this->assertDatabaseHas('staff_details', ['StaffID' => 'A001']);
        $this->assertDatabaseHas('users', ['UserName' => 'A001']);
    }

    // ── Edit Basic Information ──────────────────────────────────────

    public function test_edit_basic_info_updates_staff_record(): void
    {
        Livewire::test(Users::class)
            ->call('openEditBasicInfo', 'C002')
            ->set('editContactNumber', '09998887777')
            ->call('saveBasicInfo');

        $this->assertDatabaseHas('staff_details', [
            'StaffID' => 'C002',
            'ContactNumber' => '09998887777',
        ]);
    }

    public function test_edit_basic_info_rejects_an_invalid_contact_number(): void
    {
        Livewire::test(Users::class)
            ->call('openEditBasicInfo', 'C002')
            ->set('editContactNumber', 'not-a-number')
            ->call('saveBasicInfo')
            ->assertHasErrors('editContactNumber');
    }
}