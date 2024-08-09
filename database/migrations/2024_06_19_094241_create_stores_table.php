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
        Schema::create('stores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid');
            $table->string('name');
            $table->string('cover');
            $table->text('categories')->nullable();
            $table->text('address')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('cid')->nullable();
            $table->text('about')->nullable();
            $table->double('rating', 10, 2)->default(0);
            $table->integer('total_rating');
            $table->text('timing')->nullable();
            $table->text('images')->nullable();
            $table->text('zipcode')->nullable();
            $table->tinyInteger('in_home')->default(1);
            $table->tinyInteger('popular')->default(1);
            $table->text('extra_field')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
