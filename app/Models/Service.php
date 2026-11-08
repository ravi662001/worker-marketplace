<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Service extends Model {
 protected $fillable=['category_id','name','slug','description','base_price','duration_minutes','image','is_active'];
 protected function casts(): array { return ['base_price'=>'decimal:2','duration_minutes'=>'integer','is_active'=>'boolean']; }
 public function category(): BelongsTo { return $this->belongsTo(ServiceCategory::class,'category_id'); }
 public function bookings(): HasMany { return $this->hasMany(Booking::class); }
}
