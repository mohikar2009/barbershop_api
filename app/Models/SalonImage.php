<?php

namespace App\Models;
use App\Models\Salon;

use Illuminate\Database\Eloquent\Model;

class SalonImage extends Model
{
    protected $fillable = ['image','salon_id'];
    public function salon(){
        return $this->belongsTo(Salon::class);
    }
}
