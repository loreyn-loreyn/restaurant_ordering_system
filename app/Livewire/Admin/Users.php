<?php

namespace App\Livewire\Admin;

use App\Models\LoginLog;
use App\Models\StaffDetails;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Users extends Component
{
    // ── Create-account form ────────────────────────────────────────────
    public string $staffId = '';
    public string $birthDate = '';

    // ── Confirmation step (mirrors the "is this correct?" wireframe) ───
    public bool $confirming = false;
    public bool $confirmedCorrect = false;
    public ?array $pendingStaffPreview = null;

    // ── Success modal ───────────────────────────────────────────────────
    public bool $showSuccess = false;
    public ?string $createdUsername = null;
    public ?string $createdPassword = null;

    // ── Accounts table filter ───────────────────────────────────────────
    public string $statusFilter = 'all'; // all | active | inactive

    // ── Activate/Deactivate confirmation modal ──────────────────────────
    public ?int $confirmingToggleUserId = null;
    public ?string $confirmingToggleName = null;
    public bool $confirmingToggleCurrentStatus = false; // true = currently active

    // ── Delete staff confirmation modal ─────────────────────────────────
    public ?string $confirmingDeleteStaffId = null;
    public ?string $confirmingDeleteName = null;

    // ── Edit Basic Information modal ────────────────────────────────────
    public bool $showEditBasicInfo = false;
    public ?string $editStaffId = null; // original StaffID being edited (immutable PK)
    public string $editLastName = '';
    public string $editFirstName = '';
    public string $editMiddleName = '';
    public string $editAge = '';
    public string $editBirthDate = '';
    public string $editSex = '';
    public string $editBirthPlace = '';
    public string $editNationality = '';
    public string $editAddress = '';
    public string $editContactNumber = '';
    public string $editEmail = '';

    protected function rules(): array
    {
        return [
            'staffId' => ['required', 'string', 'max:20'],
            'birthDate' => ['required', 'date'],
        ];
    }

    protected function editBasicInfoRules(): array
    {
        return [
            'editLastName' => ['required', 'string', 'max:100'],
            'editFirstName' => ['required', 'string', 'max:100'],
            'editMiddleName' => ['nullable', 'string', 'max:100'],
            'editAge' => ['required', 'integer', 'min:16', 'max:100'],
            'editBirthDate' => ['required', 'date', 'before:today'],
            'editSex' => ['required', 'in:M,F'],
            'editBirthPlace' => ['required', 'string', 'max:150'],
            'editNationality' => ['required', 'string', 'max:100'],
            'editAddress' => ['required', 'string', 'max:255'],
            'editContactNumber' => ['required', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'editEmail' => ['required', 'email', 'max:150'],
        ];
    }

    protected function messages(): array
    {
        return [
            'staffId.required' => 'Please enter the Staff ID.',
            'birthDate.required' => 'Please enter the birthdate.',
            'editContactNumber.regex' => 'Enter a valid PH mobile number, e.g. 09171234567 or +639171234567.',
        ];
    }

    /**
     * Step 1: find the pending staff record (added by a Manager, no login
     * yet) and verify the typed birthdate matches what was recorded at
     * intake. This is Admin's identity check before a login is generated —
     * it never lets someone create a login through guesswork.
     */
    public function lookupStaff(): void
    {
        $this->validate();

        $staff = StaffDetails::whereNull('UserID')
            ->where('StaffID', strtoupper(trim($this->staffId)))
            ->first();

        if (! $staff) {
            $this->addError('staffId', 'No pending staff member found with that Staff ID. They may already have an account, or the ID is incorrect.');
            return;
        }

        if (! Carbon::parse($staff->BirthDate)->isSameDay(Carbon::parse($this->birthDate))) {
            $this->addError('birthDate', 'That birthdate does not match our records for this Staff ID.');
            return;
        }

        $staff->load('role');

        $this->pendingStaffPreview = [
            'StaffID' => $staff->StaffID,
            'FullName' => $staff->full_name,
            'Position' => $staff->role?->RoleName ?? 'Unassigned',
            'BirthDate' => Carbon::parse($staff->BirthDate)->format('F j, Y'),
        ];

        $this->confirming = true;
        $this->confirmedCorrect = false;
    }

    public function cancelConfirm(): void
    {
        $this->confirming = false;
        $this->confirmedCorrect = false;
        $this->pendingStaffPreview = null;
    }

    /**
     * Step 2: create the `users` row and link it back to staff_details.
     * UserName = StaffID, Password = BirthDate as MMDDYYYY — the same
     * convention already used in DatabaseSeeder.
     */
    public function createAccount(): void
    {
        if (! $this->confirmedCorrect) {
            $this->addError('confirmedCorrect', 'Please confirm the details above are correct before continuing.');
            return;
        }

        $staff = StaffDetails::whereNull('UserID')
            ->where('StaffID', $this->pendingStaffPreview['StaffID'] ?? null)
            ->first();

        if (! $staff) {
            $this->cancelConfirm();
            $this->addError('staffId', 'This staff record is no longer pending. Please try again.');
            return;
        }

        $rawPassword = Carbon::parse($staff->BirthDate)->format('mdY');

        $user = User::create([
            'RoleID' => $staff->RoleID,
            'UserName' => $staff->StaffID,
            'Password' => Hash::make($rawPassword),
            'DateIssued' => now()->toDateString(),
            'AccountStatus' => true,
            'AccountApprovalStatus' => true,
        ]);

        $staff->update(['UserID' => $user->UserID]);

        $this->createdUsername = $staff->StaffID;
        $this->createdPassword = $rawPassword;
        $this->showSuccess = true;

        $this->reset(['staffId', 'birthDate', 'confirming', 'confirmedCorrect', 'pendingStaffPreview']);
    }

    public function closeSuccess(): void
    {
        $this->showSuccess = false;
        $this->createdUsername = null;
        $this->createdPassword = null;
    }

    /**
     * Step 1: user clicked Deactivate/Reactivate — stash which account and
     * show the confirmation modal instead of acting immediately.
     */
    public function askToggleStatus(int $userId, string $name, bool $currentStatus): void
    {
        $this->confirmingToggleUserId = $userId;
        $this->confirmingToggleName = $name;
        $this->confirmingToggleCurrentStatus = $currentStatus;
    }

    public function cancelToggleStatus(): void
    {
        $this->confirmingToggleUserId = null;
        $this->confirmingToggleName = null;
    }

    /**
     * Step 2: user confirmed in the modal — perform the actual toggle.
     */
    public function confirmToggleStatus(): void
    {
        if ($this->confirmingToggleUserId) {
            $this->toggleStatus($this->confirmingToggleUserId);
        }

        $this->cancelToggleStatus();
    }

    /**
     * Toggle a user's active/inactive status.
     *
     * Deactivating already blocks future logins (Login.php checks
     * AccountStatus before Auth::attempt). To make it take effect
     * immediately — not just on their next login attempt — we also drop
     * any session row currently tied to that user, which forces the
     * `auth` middleware to treat them as logged out on their very next request.
     */
    public function toggleStatus(int $userId): void
    {
        $user = User::findOrFail($userId);
        $newStatus = ! $user->AccountStatus;
        $user->update(['AccountStatus' => $newStatus]);

        if (! $newStatus) {
            DB::table('sessions')->where('user_id', $userId)->delete();

            LoginLog::where('UserID', $userId)
                ->whereNull('LogoutAt')
                ->latest('LoginAt')
                ->first()
                ?->update(['LogoutAt' => now()]);
        }
    }

    public function setFilter(string $filter): void
    {
        $this->statusFilter = $filter;
    }

    // ── Edit Basic Information ───────────────────────────────────────────

    /**
     * Step 1: user clicked Delete on a staff row — stash which one and show
     * the confirmation modal instead of deleting immediately. Admin staff
     * are never deletable, so we silently refuse to even open the modal
     * for them (the button is also hidden in the view, but this re-checks
     * against the database rather than trusting the click).
     */
    public function askDeleteStaff(string $staffId, string $name): void
    {
        $staff = StaffDetails::with('role')->where('StaffID', $staffId)->first();

        if (! $staff || $staff->role?->RoleName === 'Admin') {
            return;
        }

        $this->confirmingDeleteStaffId = $staffId;
        $this->confirmingDeleteName = $name;
    }

    public function cancelDeleteStaff(): void
    {
        $this->confirmingDeleteStaffId = null;
        $this->confirmingDeleteName = null;
    }

    /**
     * Step 2: user confirmed — remove the staff record. If a login was
     * ever issued for them, remove that account too (and any active
     * session/log rows) so we don't leave an orphaned user behind.
     * Admin staff are excluded again here as a final safeguard.
     */
    public function confirmDeleteStaff(): void
    {
        $staff = StaffDetails::with('role')->where('StaffID', $this->confirmingDeleteStaffId)->first();

        if ($staff && $staff->role?->RoleName !== 'Admin') {
            if ($staff->UserID) {
                DB::table('sessions')->where('user_id', $staff->UserID)->delete();
                LoginLog::where('UserID', $staff->UserID)->delete();
                User::where('UserID', $staff->UserID)->delete();
            }

            $staff->delete();
        }

        $this->cancelDeleteStaff();
    }

    public function openEditBasicInfo(string $staffId): void
    {
        $staff = StaffDetails::where('StaffID', $staffId)->firstOrFail();

        $this->editStaffId = $staff->StaffID;
        $this->editLastName = $staff->LastName;
        $this->editFirstName = $staff->FirstName;
        $this->editMiddleName = $staff->MiddleName ?? '';
        $this->editAge = (string) $staff->Age;
        $this->editBirthDate = Carbon::parse($staff->BirthDate)->toDateString();
        $this->editSex = $staff->Sex;
        $this->editBirthPlace = $staff->BirthPlace;
        $this->editNationality = $staff->Nationality;
        $this->editAddress = $staff->Address;
        $this->editContactNumber = $staff->ContactNumber;
        $this->editEmail = $staff->Email;

        $this->resetErrorBag();
        $this->showEditBasicInfo = true;
    }

    public function updatedEditBirthDate($value): void
    {
        if ($value) {
            $this->editAge = (string) Carbon::parse($value)->age;
        }
    }

    public function closeEditBasicInfo(): void
    {
        $this->showEditBasicInfo = false;
        $this->editStaffId = null;
    }

    public function saveBasicInfo(): void
    {
        $this->validate($this->editBasicInfoRules());

        $staff = StaffDetails::where('StaffID', $this->editStaffId)->firstOrFail();

        $staff->update([
            'LastName' => $this->editLastName,
            'FirstName' => $this->editFirstName,
            'MiddleName' => $this->editMiddleName ?: null,
            'Age' => $this->editAge,
            'BirthDate' => $this->editBirthDate,
            'Sex' => $this->editSex,
            'BirthPlace' => $this->editBirthPlace,
            'Nationality' => $this->editNationality,
            'Address' => $this->editAddress,
            'ContactNumber' => $this->editContactNumber,
            'Email' => $this->editEmail,
        ]);

        $this->closeEditBasicInfo();
    }

    public function render()
    {
        $pendingStaff = StaffDetails::with('role')
            ->whereNull('UserID')
            ->orderBy('StaffID')
            ->get();

        $accounts = StaffDetails::with(['user.role', 'role'])
            ->whereNotNull('UserID')
            ->when($this->statusFilter === 'active', fn ($q) => $q->whereHas('user', fn ($uq) => $uq->where('AccountStatus', true)))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->whereHas('user', fn ($uq) => $uq->where('AccountStatus', false)))
            ->orderByDesc('UserID')
            ->get();

        // Every staff member on file (pending or with an account) — the
        // HR-style detail the Manager captured at intake. Kept as its own
        // table since it's a different kind of information than account status.
        $basicInfo = StaffDetails::with('role')->orderBy('StaffID')->get();

        return view('livewire.admin.users', [
            'pendingStaff' => $pendingStaff,
            'accounts' => $accounts,
            'basicInfo' => $basicInfo,
        ]);
    }
}