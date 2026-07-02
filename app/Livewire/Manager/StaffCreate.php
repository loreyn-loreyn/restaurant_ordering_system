<?php

namespace App\Livewire\Manager;

use App\Models\Role;
use App\Models\StaffDetails;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.manager')]
class StaffCreate extends Component
{
    use WithFileUploads;

    public bool $confirming = false;

    public string $LastName = '';
    public string $FirstName = '';
    public string $MiddleName = '';
    public string $Age = '';
    public string $BirthDate = '';
    public string $Sex = '';
    public string $BirthPlace = '';
    public string $Nationality = '';
    public string $Address = '';
    public string $ContactNumber = '';
    public string $Email = '';
    public ?int $AssignedRoleID = null;
    public string $HiredDate = '';
    public $Photo = null;

    protected function operationalRoles()
    {
        return Role::whereNotIn('RoleName', ['Admin', 'Manager'])->orderBy('RoleName')->get();
    }

    protected function rules(): array
    {
        return [
            'LastName' => ['required', 'string', 'max:100'],
            'FirstName' => ['required', 'string', 'max:100'],
            'MiddleName' => ['nullable', 'string', 'max:100'],
            'Age' => ['required', 'integer', 'min:16', 'max:100'],
            'BirthDate' => ['required', 'date', 'before:today'],
            'Sex' => ['required', 'in:M,F'],
            'BirthPlace' => ['required', 'string', 'max:150'],
            'Nationality' => ['required', 'string', 'max:100'],
            'Address' => ['required', 'string', 'max:255'],
            // PH mobile format: 09XXXXXXXXX (11 digits) or +639XXXXXXXXX
            'ContactNumber' => ['required', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'Email' => ['required', 'email', 'max:150'],
            'AssignedRoleID' => ['required', 'exists:roles,RoleID'],
            'HiredDate' => ['required', 'date'],
            'Photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            'ContactNumber.regex' => 'Enter a valid PH mobile number, e.g. 09171234567 or +639171234567.',
        ];
    }

    public function updatedBirthDate($value): void
    {
        if ($value) {
            $this->Age = (string) Carbon::parse($value)->age;
        }
    }

    public function discard(): void
    {
        $this->redirectRoute('manager.staffs', navigate: true);
    }

    public function confirmSubmit(): void
    {
        $this->validate();
        $this->confirming = true;
    }

    public function cancelConfirm(): void
    {
        $this->confirming = false;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $role = Role::findOrFail($this->AssignedRoleID);
        $prefix = strtoupper(substr($role->RoleName, 0, 1));

        $lastStaffId = StaffDetails::where('StaffID', 'like', $prefix . '%')
            ->orderByDesc('StaffID')
            ->value('StaffID');
        $nextNumber = $lastStaffId ? ((int) substr($lastStaffId, 1)) + 1 : 1;
        $staffId = $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

        $photoPath = $this->Photo ? $this->Photo->store('staff-photos', 'public') : null;

        StaffDetails::create([
            'StaffID' => $staffId,
            'UserID' => null, // Admin links this once they create the account
            'RoleID' => $this->AssignedRoleID,
            'LastName' => $this->LastName,
            'FirstName' => $this->FirstName,
            'MiddleName' => $this->MiddleName ?: null,
            'Photo' => $photoPath,
            'Age' => $this->Age,
            'BirthDate' => $this->BirthDate,
            'Sex' => $this->Sex,
            'BirthPlace' => $this->BirthPlace,
            'Nationality' => $this->Nationality,
            'Address' => $this->Address,
            'ContactNumber' => $this->ContactNumber,
            'Email' => $this->Email,
            'HiredDate' => $this->HiredDate,
        ]);

        $this->redirectRoute('manager.staffs', navigate: true);
    }

    public function render()
    {
        return view('livewire.manager.staff-create', [
            'roles' => $this->operationalRoles(),
        ]);
    }
}