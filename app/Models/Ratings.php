<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ratings extends Model
{
    use HasFactory;

    protected $table = 'rating';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['uid', 'service_id', 'store_id', 'driver_id', 'rate', 'msg', 'from', 'status', 'extra_field'];

    protected $hidden = [
        'updated_at',
        'created_at',
    ];
}
