<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminProfile extends Model
{
    protected $fillable = ['name','family','admin_credential_id'];


    public function adminCredential(){
        return $this->belongsTo(AdminCredential::class);
    }
}
      