<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class WorkerProfile extends Model {
 protected $fillable=['user_id','bio','city','state','pincode','experience_years','service_radius_km','verification_status','is_available','hourly_rate'];
 protected function casts(): array { return ['is_available'=>'boolean','hourly_rate'=>'decimal:2','experience_years'=>'integer','service_radius_km'=>'integer']; }
 public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
