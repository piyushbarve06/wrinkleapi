<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid');
            $table->integer('store_id');
            $table->integer('driver_id')->default(0);
            $table->tinyInteger('self_pickup')->default(0);
            $table->integer('order_to');
            $table->text('address');
            $table->text('items');
            $table->integer('coupon_id')->nullable();
            $table->text('coupon')->nullable();
            $table->double('discount', 10, 2);
            $table->double('distance_cost', 10, 2);
            $table->double('total', 10, 2);
            $table->double('serviceTax', 10, 2);
            $table->double('grand_total', 10, 2);
            $table->integer('pay_method');
            $table->text('paid');
            $table->date('pickup_date');
            $table->string('pickup_slot');
            $table->date('delivery_date');
            $table->string('delivery_slot');
            $table->tinyInteger('wallet_used')->default(0);
            $table->double('wallet_price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->text('extra_field')->nullable();
            $table->tinyInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
