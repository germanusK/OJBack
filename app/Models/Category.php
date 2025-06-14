<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'category_id'
    ];

    public function businesses(){
        return $this->hasMany(Business::class, 'category_id');
    }

    public function products(){
        return $this->hasMany(Product::class, 'category_id');
    }
}
