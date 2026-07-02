<?php

namespace App\Livewire\Manager;

use App\Models\Role;
use App\Models\StaffDetails;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.manager')]
class Staffs extends Component
{
    public string|int|null $roleFilter = null; // null = All, 'unassigned' = no account yet, int = RoleID

    protected function operationalRoles()
    {
        return Role::whereNotIn('RoleName', ['Admin', 'Manager'])->orderBy('RoleName')->get();
    }

    public function filterByRole(string|int|null $roleFilter): void
    {
        $this->roleFilter = $roleFilter;
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
        ]);
    }
}