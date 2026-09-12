<?php

namespace App\Models;

use App\Models\UserCredential;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = ['code', 'used_at', 'expires_at', 'user_credentials_id'];
    public function userCredential()
    {
        return $this->belongsTo(UserCredential::class, 'user_credentials_id');
    }
}
