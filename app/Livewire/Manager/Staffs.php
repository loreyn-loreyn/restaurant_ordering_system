<?php

namespace App\Livewire\Manager;

use App\Models\Role;
use App\Models\StaffDetails;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.manager')]
class Staffs extends Component
{
    public string|int|null $roleFilter = null; // null = All, 'unassigned' = no account yet, int = RoleID

    // ---- long-press action menu state ----
    public ?string $actionMenuStaffId = null;
    public ?string $pendingDeleteStaffId = null;

    protected function operationalRoles()
    {
        return Role::whereNotIn('RoleName', ['Admin', 'Manager'])->orderBy('RoleName')->get();
    }

    public function filterByRole(string|int|null $roleFilter): void
    {
        $this->roleFilter = $roleFilter;
    }

    /**
     * Long-press on a staff card opens a small Edit/Delete action menu.
     */
    public function openActionMenu(string $staffId): void
    {
        $this->actionMenuStaffId = $staffId;
    }

    public function closeActionMenu(): void
    {
        $this->actionMenuStaffId = null;
    }

    public function chooseEditFromMenu(): void
    {
        $staffId = $this->actionMenuStaffId;
        $this->actionMenuStaffId = null;

        if ($staffId) {
            $this->redirectRoute('manager.staff.edit', $staffId, navigate: true);
        }
    }

    public function chooseDeleteFromMenu(): void
    {
        $staffId = $this->actionMenuStaffId;
        $this->actionMenuStaffId = null;

        if ($staffId) {
            $this->pendingDeleteStaffId = $staffId;
        }
    }

    public function cancelDelete(): void
    {
        $this->pendingDeleteStaffId = null;
    }

    public function deleteStaff(): void
    {
        if (! $this->pendingDeleteStaffId) {
            return;
        }

        $staff = StaffDetails::find($this->pendingDeleteStaffId);

        if ($staff) {
            if ($staff->Photo) {
                Storage::disk('public')->delete($staff->Photo);
            }
            $staff->delete();
        }

        $this->pendingDeleteStaffId = null;
    }

    public function render()
    {
        $roles = $this->operationalRoles();
        $excludedRoleIds = Role::whereIn('RoleName', ['Admin', 'Manager'])->pluck('RoleID');
        $today = now()->toDateString();

        $staff = StaffDetails::with(['user.role', 'role', 'attendances' => function ($q) use ($today) {
            $q->whereDate('AttendanceDate', $today);
        }])
            // Hide Admin/Manager either way they might be tagged (own RoleID, or via linked account for legacy rows)
            ->where(function ($q) use ($excludedRoleIds) {
                $q->whereNotIn('RoleID', $excludedRoleIds)
                    ->orWhereHas('user', fn ($uq) => $uq->whereNotIn('RoleID', $excludedRoleIds));
            })
            ->when($this->roleFilter === 'unassigned', function ($q) {
                $q->whereNull('UserID');
            })
            ->when(is_int($this->roleFilter), function ($q) {
                $q->where(function ($q2) {
                    $q2->where('RoleID', $this->roleFilter)
                        ->orWhereHas('user', fn ($uq) => $uq->where('RoleID', $this->roleFilter));
                });
            })
            ->orderByRaw('UserID IS NULL DESC')
            ->get();

        return view('livewire.manager.staffs', [
            'roles' => $roles,
            'staff' => $staff,
            'actionMenuStaff' => $this->actionMenuStaffId ? StaffDetails::find($this->actionMenuStaffId) : null,
            'pendingDeleteStaff' => $this->pendingDeleteStaffId ? StaffDetails::find($this->pendingDeleteStaffId) : null,
        ]);
    }
}