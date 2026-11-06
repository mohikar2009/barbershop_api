<?php

namespace App\Models;
use App\Models\UserCredential;
use App\Models\AdminCredential;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name'];
   
   
    public function userCredential(){
        return $this->hasMany(UserCredential::class);
    }

    public function adminCredential(){
        return $this->hasMany(AdminCredential::class);
    }
}
