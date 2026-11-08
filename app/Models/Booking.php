<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'customer_id',
        'worker_id',
        'service_id',
        'customer_name',
        'customer_phone',
        'address',
        'city',
        'pincode',
        'booking_date',
        'start_time',
        'end_time',
        'notes',
        'service_price',
        'platform_fee',
        'discount',
        'total_amount',
        'status',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'service_price' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}
