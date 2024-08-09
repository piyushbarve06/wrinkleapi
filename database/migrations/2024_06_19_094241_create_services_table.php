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
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('store_id');
            $table->integer('cate_id');
            $table->string('sub_cate');
            $table->string('name');
            $table->text('cover');
            $table->double('original_price', 10, 2)->nullable();
            $table->double('sell_price', 10, 2)->nullable();
            $table->double('discount', 10, 2)->nullable();
            $table->text('variations')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->text('extra_field')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
