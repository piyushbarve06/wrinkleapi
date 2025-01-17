<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;

    protected $table = 'categories';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['name', 'cover', 'descriptions', 'status', 'extra_field'];

    protected $hidden = [
        'updated_at',
        'created_at',
    ];
}
