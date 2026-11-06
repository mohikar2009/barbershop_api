<?php

namespace App\Models;
use App\Models\Service;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Model;

class SalonService extends Model
{
    protected $fillable = ['salon_id','service_id','price','duration','is_active'];
    public function salon(){
        return $this->belongsTo(Salon::class);
    }
    public function service(){
        return $this->belongsTo(Service::class);
    }

}
