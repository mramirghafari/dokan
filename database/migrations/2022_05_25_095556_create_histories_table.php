<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->foreign('delivery_id')->on('deliveries')->references('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->on('users')->references('id');
            //افزودن - کاهش - انتقال - تحویل - موجودی اولیه
            $table->enum('action',['increment','decrement','transfer','delivery','entity','editEntity']);
            $table->string('store');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histories');
    }
};
