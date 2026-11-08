<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function create(User $customer, Service $service, array $data): Booking
    {
        return DB::transaction(function () use ($customer, $service, $data) {

            // Lock worker record to prevent simultaneous booking conflicts.
            $worker = User::query()
                ->whereKey($data['worker_id'])
                ->where('role', 'worker')
                ->whereHas('workerProfile', function ($query) {
                    $query->where('verification_status', 'approved')
                        ->where('is_available', true);
                })
                ->with('workerProfile')
                ->lockForUpdate()
                ->firstOrFail();

            $profile = $worker->workerProfile;

            if (!$profile) {
                throw ValidationException::withMessages([
                    'worker_id' => 'Worker availability profile was not found.',
                ]);
            }

            $date = $data['booking_date'];
            $time = $data['start_time'];

            $bookingStart = Carbon::parse($date . ' ' . $time);
            $bookingEnd = $bookingStart->copy()
                ->addMinutes($service->duration_minutes);

            // Check working days.
            $day = strtolower($bookingStart->englishDayOfWeek);
            $workingDays = $profile->working_days ?? [];

            if (!in_array($day, $workingDays, true)) {
                throw ValidationException::withMessages([
                    'booking_date' => 'This worker does not work on the selected day.',
                ]);
            }

            // Check working hours.
            if (!$profile->work_start_time || !$profile->work_end_time) {
                throw ValidationException::withMessages([
                    'worker_id' => 'Worker working hours are not configured.',
                ]);
            }

            $workStart = Carbon::parse($date . ' ' . $profile->work_start_time);
            $workEnd = Carbon::parse($date . ' ' . $profile->work_end_time);

            if (
                $bookingStart->lt($workStart) ||
                $bookingEnd->gt($workEnd)
            ) {
                throw ValidationException::withMessages([
                    'start_time' => 'Selected time is outside the worker working hours.',
                ]);
            }

            // Check worker holiday.
            $isHoliday = $profile->unavailableDates()
                ->whereDate('unavailable_date', $date)
                ->exists();

            if ($isHoliday) {
                throw ValidationException::withMessages([
                    'booking_date' => 'This worker is unavailable on the selected holiday.',
                ]);
            }

            // Check overlapping bookings.
            $existingBookings = Booking::query()
                ->with('service')
                ->where('worker_id', $worker->id)
                ->whereDate('booking_date', $date)
                ->whereIn('status', [
                    'pending',
                    'accepted',
                    'on_the_way',
                    'in_progress',
                ])
                ->lockForUpdate()
                ->get();

            foreach ($existingBookings as $existingBooking) {

                $existingStart = Carbon::parse(
                    $date . ' ' . $existingBooking->start_time
                );

                // Use saved end_time or calculate it for older bookings.
                if ($existingBooking->end_time) {
                    $existingEnd = Carbon::parse(
                        $date . ' ' . $existingBooking->end_time
                    );
                } else {
                    $existingDuration = $existingBooking->service->duration_minutes ?? 60;

                    $existingEnd = $existingStart->copy()
                        ->addMinutes($existingDuration);
                }

                $hasOverlap = $bookingStart->lt($existingEnd)
                    && $bookingEnd->gt($existingStart);

                if ($hasOverlap) {
                    throw ValidationException::withMessages([
                        'start_time' => 'This time slot is already booked. Please choose another time.',
                    ]);
                }
            }

            // Calculate price and platform fee.
            $price = (float) ($data['service_price'] ?? $service->base_price);
            $fee = round($price * 0.10, 2);

            // Create booking.
            return Booking::create([
                'booking_code' => 'WM-' . now()->format('ymd') . '-' . strtoupper(Str::random(6)),

                'customer_id' => $customer->id,
                'worker_id' => $worker->id,
                'service_id' => $service->id,

                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],

                'address' => $data['address'],
                'city' => $data['city'],
                'pincode' => $data['pincode'],

                'booking_date' => $date,
                'start_time' => $bookingStart->format('H:i:s'),
                'end_time' => $bookingEnd->format('H:i:s'),

                'notes' => $data['notes'] ?? null,

                'service_price' => $price,
                'platform_fee' => $fee,
                'discount' => 0,
                'total_amount' => $price + $fee,

                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);
        });
    }
}
