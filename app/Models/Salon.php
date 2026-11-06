<?php

namespace App\Models;

use App\Models\SalonImage;
use App\Models\Barber;
use App\Models\Comment;
use App\Models\Discount;
use App\Models\Booking;

use Illuminate\Database\Eloquent\Model;

class Salon extends Model
{
    protected $fillable = ['name', 'address', 'phone', 'description', 'is_active'];

    public function salonImages()
    {
        return $this->hasMany(SalonImage::class);
    }


    public function barbers()
    {
        return $this->hasMany(Barber::class);
    }

    public function salonServices()
    {
        return $this->hasMany(SalonService::class);
    }


    public function imageGalleries()
    {
        return $this->hasMany(ImageGallery::class);
    }


    public function comments()
    {
        return $this->hasMany(Comment::class);
    }


    public function discounts(){
        return $this->hasMany(Discount::class);
    }

     public function bookings(){
        return $this->hasMany(Booking::class);
    }



}
