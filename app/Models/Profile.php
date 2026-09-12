<?php

namespace App\Models;
use App\Models\UserCredential;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable=['name','family','age','email','user_credentials_id'];
    public function userCredential(){
        return $this->belongsTo(UserCredential::class,'user_credentials_id');
    }
}
