<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = ['title', 'percent', 'start_at', 'end_at', 'salon_id','is_active'];
    
    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }

}
