<?php

namespace App\Livewire\Admin;

use App\Models\LoginLog;
use App\Models\Order;
use App\Models\StaffDetails;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        // "Pending Account Approvals" = staff the Manager has added but
        // Admin hasn't created a login for yet (staff_details.UserID is null).
        $pendingApprovals = StaffDetails::whereNull('UserID')->count();

        $ordersCount = Order::count();
        $employeeCount = User::count();

        // Accounts created within the last 30 days. Password is intentionally
        // never shown here — the "Account Created" date substitutes for it.
        $recentAccounts = StaffDetails::with('user')
            ->whereNotNull('UserID')
            ->whereHas('user', fn ($q) => $q->where('DateIssued', '>=', Carbon::now()->subMonth()))
            ->orderByDesc('UserID')
            ->limit(5)
            ->get();

        $recentStaff = StaffDetails::with(['user.role', 'role'])
            ->orderByDesc('UserID')
            ->limit(5)
            ->get();

        // ── User Logs ────────────────────────────────────────────────────
        // "Online" is read from the `sessions` table (does a session row for
        // this user still exist?) rather than purely from LogoutAt. That way
        // someone whose browser crashed or lost connection without hitting
        // /logout still shows correctly as no-longer-online once their
        // session actually expires — and if their most recent log has no
        // LogoutAt *and* no session, that combination itself is the signal
        // that they never cleanly logged out.
        //
        // NOTE: this starts from `User`, not `StaffDetails`. LoginLog rows
        // get written for every login regardless of role, but not every
        // account necessarily has a linked staff_details row (e.g. Admin,
        // Cashier, or Kitchen Staff accounts seeded directly rather than
        // created through the Manager → Admin staff-onboarding flow).
        // Starting from StaffDetails would silently drop those accounts
        // from this table even though they log in and out normally.
        $userLogs = User::with('staffDetails')
            ->get()
            ->map(function ($user) {
                $latest = LoginLog::where('UserID', $user->UserID)->latest('LoginAt')->first();
                $isOnline = DB::table('sessions')->where('user_id', $user->UserID)->exists();

                return (object) [
                    'StaffID' => $user->staffDetails?->StaffID ?? $user->UserName,
                    'Name' => $user->staffDetails?->full_name ?? $user->UserName,
                    'LastLogin' => $latest?->LoginAt,
                    'LastLogout' => $latest?->LogoutAt,
                    'IsOnline' => $isOnline,
                ];
            })
            ->filter(fn ($row) => $row->LastLogin !== null)
            ->sortByDesc(fn ($row) => $row->LastLogin)
            ->take(8)
            ->values();

        return view('livewire.admin.dashboard', [
            'pendingApprovals' => $pendingApprovals,
            'ordersCount' => $ordersCount,
            'employeeCount' => $employeeCount,
            'recentAccounts' => $recentAccounts,
            'recentStaff' => $recentStaff,
            'userLogs' => $userLogs,
        ]);
    }
}