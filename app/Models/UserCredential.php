<?php

namespace App\Models;

use App\Models\Profile;
use App\Models\OtpCode;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class UserCredential extends Model
{
   use HasApiTokens;
   protected $fillable = ['phone', 'password', 'is_deleted'];
   public function profile()
   {
      return $this->hasOne(Profile::class, 'user_credentials_id');
   }
   public function otpCodes()
   {
      return $this->hasMany(OtpCode::class, 'user_credentials_id');
   }
}
