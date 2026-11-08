<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerUnavailableDate extends Model
{
    protected $fillable = [
        'worker_profile_id',
        'unavailable_date',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'unavailable_date' => 'date',
        ];
    }

    public function workerProfile(): BelongsTo
    {
        return $this->belongsTo(WorkerProfile::class);
    }
}