<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageGallery extends Model
{
    protected $fillable = ['image','salon_id','is_active'];
    public function salon(){
        return $this->belongsTo(Salon::class);
    }
}
