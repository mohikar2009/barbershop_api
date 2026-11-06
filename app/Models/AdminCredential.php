<?php

namespace App\Models;

use App\Models\Role;
use App\Models\AdminProfile;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class AdminCredential extends Model
{
    use HasApiTokens;
    
    protected $fillable = ['password', 'phone', 'role_id', 'is_active'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function adminProfile()
    {
        return $this->hasOne(AdminProfile::class);
    }
}
