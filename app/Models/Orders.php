<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $table = 'orders';

    public $timestamps = true; //by default timestamp false

    protected $fillable = [
        'uid',
        'store_id',
        'driver_id',
        'self_pickup',
        'order_to',
        'address',
        'items',
        'coupon_id',
        'coupon',
        'discount',
        'distance_cost',
        'total',
        'serviceTax',
        'grand_total',
        'pay_method',
        'paid',
        'pickup_date',
        'pickup_slot',
        'delivery_date',
        'delivery_slot',
        'wallet_used',
        'wallet_price',
        'notes',
        'status',
        'extra_field'
    ];

    protected $hidden = [
        'updated_at',
        'created_at',
    ];
}
