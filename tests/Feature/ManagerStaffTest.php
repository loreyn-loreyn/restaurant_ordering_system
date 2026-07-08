<?php

namespace Tests\Feature;

use App\Livewire\Manager\StaffCreate;
use App\Livewire\Manager\StaffDetail;
use App\Livewire\Manager\Staffs;
use App\Models\Role;
use App\Models\StaffDetails;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManagerStaffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('UserName', 'M004')->firstOrFail());
    }

    protected function validStaffFields(): array
    {
        return [
            'LastName' => 'Cruz',
            'FirstName' => 'Ana',
            'MiddleName' => '',
            'Age' => '28',
            'BirthDate' => '1997-05-10',
            'Sex' => 'F',
            'BirthPlace' => 'Cebu',
            'Nationality' => 'Filipino',
            'Address' => '1 Test St',
            'ContactNumber' => '09171112233',
            'Email' => 'ana.cruz@example.com',
            'HiredDate' => now()->toDateString(),
        ];
    }

    // ── StaffCreate: ID generation ──────────────────────────────────

    public function test_new_staff_gets_a_prefixed_incrementing_staff_id(): void
    {
        $cashierRole = Role::where('RoleName', 'Cashier')->firstOrFail();

        // Seeded Cashiers are C002 and C005, so the next should be C006.
        $component = Livewire::test(StaffCreate::class);
        foreach ($this->validStaffFields() as $key => $value) {
            $component->set($key, $value);
        }
        $component->set('AssignedRoleID', $cashierRole->RoleID)->call('save');

        $this->assertDatabaseHas('staff_details', [
            'StaffID' => 'C006',
            'UserID' => null, // Admin links the account later
        ]);
    }

    public function test_new_staff_starts_unlinked_pending_admin_review(): void
    {
        $kitchenRole = Role::where('RoleName', 'Kitchen Staff')->firstOrFail();

        $component = Livewire::test(StaffCreate::class);
        foreach ($this->validStaffFields() as $key => $value) {
            $component->set($key, $value);
        }
        $component->set('AssignedRoleID', $kitchenRole->RoleID)->call('save');

        $staff = StaffDetails::where('RoleID', $kitchenRole->RoleID)->latest('StaffID')->first();
        $this->assertNull($staff->UserID);
        $this->assertFalse($staff->has_account);
    }

    /**
     * Documents a real bug rather than "fixing" it: the ID-generation
     * logic assumes a single-character prefix and does substr($lastId, 1)
     * to find the numeric tail. Two roles that happen to share a first
     * letter would collide on the same StaffID sequence. Manager and
     * Admin are excluded from this form, but Kitchen Staff ('K') already
     * demonstrates the single-char-prefix assumption in action.
     */
    public function test_staff_id_prefix_is_a_single_uppercase_letter_from_the_role_name(): void
    {
        $kitchenRole = Role::where('RoleName', 'Kitchen Staff')->firstOrFail();

        $component = Livewire::test(StaffCreate::class);
        foreach ($this->validStaffFields() as $key => $value) {
            $component->set($key, $value);
        }
        $component->set('AssignedRoleID', $kitchenRole->RoleID)->call('save');

        // Seeded Kitchen Staff are K003, K007 -> next is K008.
        $this->assertDatabaseHas('staff_details', ['StaffID' => 'K008']);
    }

    public function test_editing_staff_does_not_change_the_staff_id(): void
    {
        $staff = StaffDetails::where('StaffID', 'C002')->firstOrFail();

        Livewire::test(StaffCreate::class, ['staffDetail' => $staff])
            ->set('LastName', 'UpdatedLastName')
            ->call('save');

        $staff->refresh();
        $this->assertSame('C002', $staff->StaffID);
        $this->assertSame('UpdatedLastName', $staff->LastName);
    }

    public function test_birthdate_auto_computes_age(): void
    {
        Livewire::test(StaffCreate::class)
            ->set('BirthDate', now()->subYears(25)->toDateString())
            ->assertSet('Age', '25');
    }

    public function test_validation_requires_valid_ph_contact_number(): void
    {
        $component = Livewire::test(StaffCreate::class);
        foreach ($this->validStaffFields() as $key => $value) {
            $component->set($key, $value);
        }
        $component
            ->set('ContactNumber', '12345')
            ->set('AssignedRoleID', Role::where('RoleName', 'Cashier')->value('RoleID'))
            ->call('confirmSubmit')
            ->assertHasErrors('ContactNumber');
    }

    // ── Staffs listing ───────────────────────────────────────────────

    public function test_admin_and_manager_are_excluded_from_the_staff_listing(): void
    {
        $staff = Livewire::test(Staffs::class)->viewData('staff');

        $this->assertFalse($staff->contains('StaffID', 'A001'));
        $this->assertFalse($staff->contains('StaffID', 'M004'));
    }

    public function test_filter_unassigned_shows_only_staff_without_a_login(): void
    {
        StaffDetails::create([
            'StaffID' => 'X999',
            'UserID' => null,
            'RoleID' => Role::where('RoleName', 'Server')->value('RoleID'),
            'LastName' => 'Pending', 'FirstName' => 'Staff', 'MiddleName' => null,
            'Age' => 22, 'BirthDate' => '2004-01-01', 'Sex' => 'F',
            'BirthPlace' => 'Manila', 'Nationality' => 'Filipino',
            'Address' => 'Nowhere', 'ContactNumber' => '09990001111',
            'Email' => 'x999@example.com', 'HiredDate' => now()->toDateString(),
        ]);

        $staff = Livewire::test(Staffs::class)
            ->call('filterByRole', 'unassigned')
            ->viewData('staff');

        $this->assertTrue($staff->contains('StaffID', 'X999'));
        $this->assertFalse($staff->contains('StaffID', 'C002')); // has an account
    }

    public function test_filter_by_role_id(): void
    {
        $kitchenRoleId = Role::where('RoleName', 'Kitchen Staff')->value('RoleID');

        $staff = Livewire::test(Staffs::class)
            ->call('filterByRole', $kitchenRoleId)
            ->viewData('staff');

        $this->assertTrue($staff->every(fn ($s) => $s->RoleID === $kitchenRoleId));
    }

    public function test_delete_staff_removes_the_record(): void
    {
        $staff = StaffDetails::where('StaffID', 'K007')->firstOrFail();

        Livewire::test(Staffs::class)
            ->call('openActionMenu', 'K007')
            ->call('chooseDeleteFromMenu')
            ->call('deleteStaff');

        $this->assertDatabaseMissing('staff_details', ['StaffID' => 'K007']);
        // Deleting a staff record does not itself touch their `users` row.
        $this->assertDatabaseHas('users', ['UserID' => $staff->UserID]);
    }

    // ── StaffDetail: attendance ──────────────────────────────────────

    public function test_marking_attendance_creates_a_new_row_for_a_fresh_date(): void
    {
        $staff = StaffDetails::where('StaffID', 'C002')->firstOrFail();
        $futureDate = now()->addDays(3)->toDateString();

        Livewire::test(StaffDetail::class, ['staffDetail' => $staff])
            ->call('selectDate', $futureDate)
            ->call('markStatus', 'P');

        $this->assertDatabaseHas('attendances', [
            'StaffID' => 'C002',
            'Status' => 'P',
        ]);
    }

    public function test_marking_attendance_twice_on_the_same_date_updates_not_duplicates(): void
    {
        $staff = StaffDetails::where('StaffID', 'C002')->firstOrFail();
        $date = now()->addDays(5)->toDateString();

        $component = Livewire::test(StaffDetail::class, ['staffDetail' => $staff]);
        $component->call('selectDate', $date)->call('markStatus', 'A');
        $component->call('markStatus', 'L');

        $this->assertSame(
            1,
            \App\Models\Attendance::where('StaffID', 'C002')
                ->whereDate('AttendanceDate', $date)
                ->count()
        );
        $this->assertDatabaseHas('attendances', ['StaffID' => 'C002', 'Status' => 'L']);
    }
}