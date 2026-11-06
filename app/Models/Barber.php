<?php

namespace App\Models;

use App\Models\Salon;
use App\Models\Service;

use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    protected $fillable = ['name', 'family', 'phone', 'job_title', 'description', 'is_active', 'salon_id'];
    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }
    public function services()
    {
        return $this->belongsToMany(Service::class,'barber_services')->withTimestamps();
    }
}
