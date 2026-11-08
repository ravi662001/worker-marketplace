<?php

namespace App\Http\Controllers;

use App\Models\WorkerProfile;
use App\Models\Booking;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function registerForm()
    {
        return view('worker.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'bio' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'digits:6'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'service_radius_km' => ['required', 'integer', 'min:1', 'max:100'],
            'working_days' => ['required', 'array', 'min:1'],
            'working_days.*' => [
                'required',
                'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
            ],
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => [
                'required',
                'date_format:H:i',
                'after:work_start_time'
            ],
        ]);

        WorkerProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data + [
                'verification_status' => 'pending',
                'is_available' => false,
            ]
        );

        $request->user()->update(['role' => 'worker']);

        return redirect()
            ->route('worker.dashboard')
            ->with('success', 'Worker application submitted. Admin approval is required.');
    }

    public function dashboard(Request $request)
    {
        $profile = $request->user()->workerProfile;

        $holidays = $profile
            ? $profile->unavailableDates()->orderBy('unavailable_date')->get()
            : collect();

        $bookings = Booking::with(['service', 'customer'])
            ->where('worker_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('worker.dashboard', compact(
            'profile',
            'bookings',
            'holidays'
        ));
    }

    public function updateAvailability(Request $request)
    {
        $profile = WorkerProfile::where('user_id', $request->user()->id)
            ->firstOrFail();

        $data = $request->validate([
            'working_days' => ['required', 'array', 'min:1'],
            'working_days.*' => [
                'required',
                'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
            ],
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => [
                'required',
                'date_format:H:i',
                'after:work_start_time'
            ],
        ]);

        $profile->update($data);

        return redirect()
            ->route('worker.dashboard')
            ->with('success', 'Your availability has been updated successfully.');
    }

    public function addUnavailableDate(Request $request)
    {
        $profile = WorkerProfile::where('user_id', $request->user()->id)
            ->firstOrFail();

        $data = $request->validate([
            'unavailable_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $profile->unavailableDates()->updateOrCreate(
            ['unavailable_date' => $data['unavailable_date']],
            ['reason' => $data['reason'] ?? null]
        );

        return redirect()
            ->route('worker.dashboard')
            ->with('success', 'Holiday date added successfully.');
    }

    public function removeUnavailableDate(Request $request, int $id)
    {
        $profile = WorkerProfile::where('user_id', $request->user()->id)
            ->firstOrFail();

        $holiday = $profile->unavailableDates()->findOrFail($id);

        $holiday->delete();

        return redirect()
            ->route('worker.dashboard')
            ->with('success', 'Holiday date removed successfully.');
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        abort_unless($booking->worker_id === $request->user()->id, 403);

        $data = $request->validate([
            'status' => [
                'required',
                'in:accepted,rejected,on_the_way,in_progress,completed'
            ],
        ]);

        $allowed = [
            'pending' => ['accepted', 'rejected'],
            'accepted' => ['on_the_way', 'in_progress'],
            'on_the_way' => ['in_progress'],
            'in_progress' => ['completed'],
        ];

        abort_unless(
            in_array($data['status'], $allowed[$booking->status] ?? [], true),
            422,
            'Invalid booking status transition.'
        );

        $booking->update(['status' => $data['status']]);

        return back()->with('success', 'Booking status updated.');
    }
}
