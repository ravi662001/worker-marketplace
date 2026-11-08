<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function create(User $customer, Service $service, array $data): Booking
    {
        return DB::transaction(function () use ($customer, $service, $data) {
            $worker = User::query()->whereKey($data['worker_id'])->where('role', 'worker')
                ->whereHas('workerProfile', fn($q) => $q->where('verification_status', 'approved')->where('is_available', true))
                ->lockForUpdate()->firstOrFail();

            $date = $data['booking_date'];
            $time = $data['start_time'];
            $conflict = Booking::query()->where('worker_id', $worker->id)
                ->whereDate('booking_date', $date)
                ->whereIn('status', ['pending','accepted','on_the_way','in_progress'])
                ->where('start_time', $time)->lockForUpdate()->exists();
            if ($conflict) {
                throw ValidationException::withMessages(['start_time' => 'This worker already has a booking at that time. Please choose another slot.']);
            }

            $price = (float) ($data['service_price'] ?? $service->base_price);
            $fee = round($price * 0.10, 2);
            return Booking::create([
                'booking_code' => 'WM-' . now()->format('ymd') . '-' . strtoupper(Str::random(6)),
                'customer_id' => $customer->id,
                'worker_id' => $worker->id,
                'service_id' => $service->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'address' => $data['address'], 'city' => $data['city'], 'pincode' => $data['pincode'],
                'booking_date' => $date, 'start_time' => $time, 'notes' => $data['notes'] ?? null,
                'service_price' => $price, 'platform_fee' => $fee, 'discount' => 0,
                'total_amount' => $price + $fee, 'status' => 'pending', 'payment_status' => 'unpaid',
            ]);
        });
    }
}
