<?php

namespace App\Models;
use App\Models\Salon;
use App\Models\Profile;


use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['profile_id','salon_id','rating', 'text', 'status', 'is_active'];
   
    public function salon(){
        return $this->belongsTo(Salon::class);
    }
      public function profile(){
        return $this->belongsTo(Profile::class);
    }
}
