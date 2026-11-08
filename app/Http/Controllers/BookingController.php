<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Review;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function create(Service $service)
    {
        $workers = User::where('role', 'worker')
            ->whereHas('workerProfile', function ($query) {
                $query->where('verification_status', 'approved')
                    ->where('is_available', true);
            })
            ->with('workerProfile')
            ->get();

        return view('bookings.create', compact('service', 'workers'));
    }

    /**
     * Return available time slots for the selected worker and date.
     */
    public function availableSlots(Request $request, Service $service)
    {
        $data = $request->validate([
            'worker_id' => ['required', 'exists:users,id'],
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $worker = User::where('id', $data['worker_id'])
            ->where('role', 'worker')
            ->whereHas('workerProfile', function ($query) {
                $query->where('verification_status', 'approved')
                    ->where('is_available', true);
            })
            ->with('workerProfile')
            ->first();

        if (!$worker || !$worker->workerProfile) {
            return response()->json(['slots' => []]);
        }

        $profile = $worker->workerProfile;
        $date = $data['booking_date'];

        // Check working day.
        $selectedDate = Carbon::parse($date);
        $day = strtolower($selectedDate->englishDayOfWeek);

        if (!in_array($day, $profile->working_days ?? [], true)) {
            return response()->json(['slots' => []]);
        }

        // Check configured working hours.
        if (!$profile->work_start_time || !$profile->work_end_time) {
            return response()->json(['slots' => []]);
        }

        // Check worker holiday.
        $isHoliday = $profile->unavailableDates()
            ->whereDate('unavailable_date', $date)
            ->exists();

        if ($isHoliday) {
            return response()->json(['slots' => []]);
        }

        $workStart = Carbon::parse($date . ' ' . $profile->work_start_time);
        $workEnd = Carbon::parse($date . ' ' . $profile->work_end_time);

        $duration = (int) $service->duration_minutes;

        if ($duration <= 0) {
            return response()->json(['slots' => []]);
        }

        // Fetch existing active bookings for this worker and date.
        $existingBookings = Booking::with('service')
            ->where('worker_id', $worker->id)
            ->whereDate('booking_date', $date)
            ->whereIn('status', [
                'pending',
                'accepted',
                'on_the_way',
                'in_progress',
            ])
            ->get();

        $bookedIntervals = [];

        foreach ($existingBookings as $booking) {
            $existingStart = Carbon::parse(
                $date . ' ' . $booking->start_time
            );

            if ($booking->end_time) {
                $existingEnd = Carbon::parse(
                    $date . ' ' . $booking->end_time
                );
            } else {
                $existingDuration = $booking->service?->duration_minutes ?? 60;

                $existingEnd = $existingStart->copy()
                    ->addMinutes($existingDuration);
            }

            $bookedIntervals[] = [
                'start' => $existingStart,
                'end' => $existingEnd,
            ];
        }

        $slots = [];

        // Generate slots at 30-minute intervals.
        $slotStart = $workStart->copy();
        $now = now();

        while (
            $slotStart->copy()->addMinutes($duration)->lte($workEnd)
        ) {
            $slotEnd = $slotStart->copy()->addMinutes($duration);

            // Do not show past slots for today's date.
            if ($slotStart->gt($now)) {
                $hasOverlap = false;

                foreach ($bookedIntervals as $interval) {
                    if (
                        $slotStart->lt($interval['end']) &&
                        $slotEnd->gt($interval['start'])
                    ) {
                        $hasOverlap = true;
                        break;
                    }
                }

                if (!$hasOverlap) {
                    $slots[] = $slotStart->format('H:i');
                }
            }

            $slotStart->addMinutes(30);
        }

        return response()->json([
            'slots' => $slots,
        ]);
    }

    public function store(
        Request $request,
        Service $service,
        BookingService $bookingService
    ) {
        $data = $request->validate([
            'worker_id' => ['required', 'exists:users,id'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'digits:6'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = $bookingService->create(
            $request->user(),
            $service,
            $data
        );

        return redirect()
            ->route('bookings.index')
            ->with(
                'success',
                'Booking request submitted. Booking ID: ' . $booking->booking_code
            );
    }

    public function index(Request $request)
    {
        $bookings = Booking::with(['service', 'worker'])
            ->where('customer_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function review(Request $request, Booking $booking)
    {
        abort_unless(
            $booking->customer_id === $request->user()->id &&
            $booking->status === 'completed' &&
            !$booking->review,
            403
        );

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        Review::create($data + [
            'booking_id' => $booking->id,
            'customer_id' => $request->user()->id,
            'worker_id' => $booking->worker_id,
        ]);

        return back()->with('success', 'Thank you for your review.');
    }
}
