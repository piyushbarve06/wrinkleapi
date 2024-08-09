<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;

    protected $table = 'services';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['store_id', 'cate_id', 'sub_cate', 'name', 'cover', 'original_price', 'sell_price', 'discount', 'variations', 'status', 'extra_field'];

    protected $hidden = [
        'updated_at',
        'created_at',
    ];
}
