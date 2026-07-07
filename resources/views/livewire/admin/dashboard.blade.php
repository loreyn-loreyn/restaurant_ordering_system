<div class="p-8 space-y-6" x-data="{ now: new Date() }" x-init="setInterval(() => now = new Date(), 1000)">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Admin Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">Overview of accounts, orders, and pending approvals.</p>
        </div>
        <div class="text-right text-sm text-slate-500">
            <div x-text="now.toLocaleDateString('en-US', { timeZone: 'Asia/Manila', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"></div>
            <div class="font-medium text-slate-700" x-text="now.toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit' }) + ' PHT'"></div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-3 gap-4">
        <a href="{{ route('admin.users') }}" wire:navigate
           class="bg-white rounded-xl shadow-sm p-5 border border-slate-200 hover:border-slate-300 transition">
            <p class="text-sm text-slate-500">Pending Account Approvals</p>
            <p class="text-3xl font-semibold text-slate-800 mt-2">{{ $pendingApprovals }}</p>
        </a>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-200">
            <p class="text-sm text-slate-500">Orders</p>
            <p class="text-3xl font-semibold text-slate-800 mt-2">{{ number_format($ordersCount) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-200">
            <p class="text-sm text-slate-500">Number of Employees / Users</p>
            <p class="text-3xl font-semibold text-slate-800 mt-2">{{ $employeeCount }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">

        {{-- Recent staff / accounts --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-72">
            <div class="px-5 py-4 border-b border-slate-200 shrink-0">
                <h2 class="font-semibold text-slate-800">Staff Overview</h2>
            </div>
            <div class="flex-1 overflow-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-left sticky top-0">
                        <tr>
                            <th class="px-5 py-2 font-medium">Staff ID</th>
                            <th class="px-5 py-2 font-medium">Name</th>
                            <th class="px-5 py-2 font-medium">Position</th>
                            <th class="px-5 py-2 font-medium">Account Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentStaff as $staff)
                            <tr>
                                <td class="px-5 py-2 text-slate-700">{{ $staff->StaffID }}</td>
                                <td class="px-5 py-2 text-slate-700">{{ $staff->full_name }}</td>
                                <td class="px-5 py-2 text-slate-500">{{ $staff->section ?? '—' }}</td>
                                <td class="px-5 py-2">
                                    @if (! $staff->has_account)
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700">Pending</span>
                                    @elseif ($staff->user->AccountStatus)
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700">Active</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-slate-200 text-slate-600">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-4 text-center text-slate-400">No staff records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recently created accounts — password column replaced with creation date --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-72">
            <div class="px-5 py-4 border-b border-slate-200 shrink-0">
                <h2 class="font-semibold text-slate-800">Last Month Created User IDs</h2>
            </div>
            <div class="flex-1 overflow-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-left sticky top-0">
                        <tr>
                            <th class="px-5 py-2 font-medium">Staff ID</th>
                            <th class="px-5 py-2 font-medium">Name</th>
                            <th class="px-5 py-2 font-medium">Account Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentAccounts as $staff)
                            <tr>
                                <td class="px-5 py-2 text-slate-700">{{ $staff->StaffID }}</td>
                                <td class="px-5 py-2 text-slate-700">{{ $staff->full_name }}</td>
                                <td class="px-5 py-2 text-slate-500">{{ \Carbon\Carbon::parse($staff->user->DateIssued)->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-4 text-center text-slate-400">No accounts created in the last month.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- User Logs --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-72">
        <div class="px-5 py-4 border-b border-slate-200 shrink-0">
            <h2 class="font-semibold text-slate-800">User Logs</h2>
            <p class="text-xs text-slate-400 mt-0.5">Status reflects whether a session for that user still exists — a blank "Logout" with an Offline status usually means the session expired without a clean sign-out (crash, closed tab, etc).</p>
        </div>
        <div class="flex-1 overflow-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left sticky top-0">
                    <tr>
                        <th class="px-5 py-2 font-medium">Staff ID</th>
                        <th class="px-5 py-2 font-medium">Name</th>
                        <th class="px-5 py-2 font-medium">Last Login</th>
                        <th class="px-5 py-2 font-medium">Last Logout</th>
                        <th class="px-5 py-2 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($userLogs as $log)
                        <tr>
                            <td class="px-5 py-2 text-slate-700 font-medium">{{ $log->StaffID }}</td>
                            <td class="px-5 py-2 text-slate-700">{{ $log->Name }}</td>
                            <td class="px-5 py-2 text-slate-500">{{ $log->LastLogin?->timezone('Asia/Manila')->format('M j, Y g:i A') ?? '—' }}</td>
                            <td class="px-5 py-2 text-slate-500">{{ $log->LastLogout?->timezone('Asia/Manila')->format('M j, Y g:i A') ?? '—' }}</td>
                            <td class="px-5 py-2">
                                @if ($log->IsOnline)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700">Online</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-slate-200 text-slate-600">Offline</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-4 text-center text-slate-400">No login activity recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>