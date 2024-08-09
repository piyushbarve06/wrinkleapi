<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    use HasFactory;

    protected $table = 'favourites';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['uid', 'store_uid', 'status', 'extra_field'];

    protected $hidden = [
        'updated_at',
        'created_at',
    ];
}
