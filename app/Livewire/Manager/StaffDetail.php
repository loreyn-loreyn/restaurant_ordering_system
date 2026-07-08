<?php

namespace App\Livewire\Manager;

use App\Models\Attendance;
use App\Models\StaffDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.manager')]
class StaffDetail extends Component
{
    use WithFileUploads;

    public StaffDetails $staff;

    public string $selectedMonth; // Y-m
    public string $selectedDate;  // Y-m-d
    public string $noteText = '';

    // ---- photo upload state ----
    public $Photo = null;

    public function mount(StaffDetails $staffDetail): void
    {
        $this->staff = $staffDetail->load('user.role');
        $this->selectedMonth = now()->format('Y-m');
        $this->selectedDate = now()->toDateString();
        $this->loadNote();
    }

    protected function loadNote(): void
    {
        $attendance = $this->staff->attendances()
            ->whereDate('AttendanceDate', $this->selectedDate)
            ->first();

        $this->noteText = $attendance->Note ?? '';
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->loadNote();
    }

    public function prevMonth(): void
    {
        $this->selectedMonth = Carbon::parse($this->selectedMonth . '-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->selectedMonth = Carbon::parse($this->selectedMonth . '-01')->addMonth()->format('Y-m');
    }

    public function markStatus(string $status): void
    {
        $attendance = $this->staff->attendances()
            ->whereDate('AttendanceDate', $this->selectedDate)
            ->first();

        if ($attendance) {
            $attendance->update(['Status' => $status, 'Note' => $this->noteText]);
        } else {
            Attendance::create([
                'StaffID' => $this->staff->StaffID,
                'AttendanceDate' => $this->selectedDate,
                'Status' => $status,
                'Note' => $this->noteText,
            ]);
        }
    }

    public function saveNote(): void
    {
        $attendance = $this->staff->attendances()
            ->whereDate('AttendanceDate', $this->selectedDate)
            ->first();

        if ($attendance) {
            $attendance->update(['Note' => $this->noteText]);
        }
    }

    protected function rules(): array
    {
        return [
            // Only real picture files are allowed (no svg/bmp/etc.), capped at 25MB
            'Photo' => ['image', 'mimes:jpeg,png,jpg', 'max:25600'],
        ];
    }

    protected function messages(): array
    {
        return [
            'Photo.image' => 'Only image files are allowed (JPG or PNG).',
            'Photo.mimes' => 'Only image files are allowed (JPG or PNG).',
            'Photo.max' => 'Image must not be larger than 25MB.',
        ];
    }

    /**
     * Validate the photo the instant it's selected, so picking a non-image
     * file is rejected immediately instead of waiting for Save.
     */
    public function updatedPhoto(): void
    {
        try {
            $this->validateOnly('Photo');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->Photo = null;
            throw $e;
        }
    }

    public function savePhoto(): void
    {
        $this->validate([
            'Photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:25600'],
        ], $this->messages());

        if ($this->staff->Photo) {
            Storage::disk('public')->delete($this->staff->Photo);
        }

        $path = $this->Photo->store('staff-photos', 'public');
        $this->staff->update(['Photo' => $path]);

        $this->Photo = null;
    }

    public function render()
    {
        $monthStart = Carbon::parse($this->selectedMonth . '-01')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $monthAttendances = $this->staff->attendances()
            ->whereBetween('AttendanceDate', [$monthStart, $monthEnd])
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->AttendanceDate)->toDateString());

        $presentCount = $this->staff->attendances()->where('Status', 'P')->count();
        $absentCount = $this->staff->attendances()->where('Status', 'A')->count();
        $leaveCount = $this->staff->attendances()->where('Status', 'L')->count();

        $selectedAttendance = $monthAttendances->get($this->selectedDate);

        return view('livewire.manager.staff-detail', [
            'monthStart' => $monthStart,
            'monthAttendances' => $monthAttendances,
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'leaveCount' => $leaveCount,
            'selectedAttendance' => $selectedAttendance,
        ]);
    }
}