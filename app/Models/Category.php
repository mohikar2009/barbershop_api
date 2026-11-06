<?php

namespace App\Models;
use App\Models\Service;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name','is_active'];
   public function service(){
    return $this->hasMany(Service::class);
   }
}
