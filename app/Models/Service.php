<?php

namespace App\Models;
use App\Models\SalonService;
use App\Models\Category;
use App\Models\Barber;
use App\Models\Booking;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name','description','is_active','category_id'];
    
    
    public function salonServices(){
        return $this->hasMany(SalonService::class);
    }
   
    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function barbers(){
        return $this->belongsToMany(Barber::class,'barber_services')->withTimestamps();
    }
     public function bookings(){
        return $this->hasMany(Booking::class);
    }
}
  