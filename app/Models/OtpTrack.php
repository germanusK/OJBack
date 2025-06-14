<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpTrack extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'otp', 'created_at', 'expires_at', 'used'];

    protected $dates = ['created_at', 'expires_at'];
}
