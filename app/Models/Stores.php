<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stores extends Model
{
    use HasFactory;

    protected $table = 'stores';

    public $timestamps = true; //by default timestamp false

    protected $fillable = [
        'uid',
        'name',
        'cover',
        'categories',
        'address',
        'lat',
        'lng',
        'about',
        'rating',
        'total_rating',
        'timing',
        'images',
        'zipcode',
        'cid',
        'status',
        'in_home',
        'popular',
        'extra_field'
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
    ];

    protected $casts = [
        'uid' => 'integer',
        'cid' => 'integer',
        'total_rating' => 'integer',
        'status' => 'integer',
    ];
}
