<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['salon_id', 'service_id', 'profile_id', 'booking_date', 'start_time', 'end_time', 'original_price', 'discount_percent', 'discount_amount', 'final_price', 'status'];

    protected $casts = [
        'booking_date' => 'date',
        'original_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
    ];

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
