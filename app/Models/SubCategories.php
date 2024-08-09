<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategories extends Model
{
    use HasFactory;

    protected $table = 'sub_categories';

    public $timestamps = true; //by default timestamp false

    protected $fillable = ['cate_id', 'name', 'cover', 'status', 'extra_field'];

    protected $hidden = [
        'updated_at',
        'created_at',
    ];
}
