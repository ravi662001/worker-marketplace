<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = ['name', 'email', 'phone', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at' => 'datetime', 'password' => 'hashed']; }
    public function workerProfile(): HasOne { return $this->hasOne(WorkerProfile::class); }
    public function customerBookings(): HasMany { return $this->hasMany(Booking::class, 'customer_id'); }
    public function workerBookings(): HasMany { return $this->hasMany(Booking::class, 'worker_id'); }
}
