<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'address', 'email', 'tel', 'whatsapp', 'logo', 'is_approved', 'category_id', 'business_id', 'user_id'
    ];

}
