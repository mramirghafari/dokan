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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fromOrganization');
            $table->foreign('fromOrganization')->on('organizations')->references('id');
            $table->unsignedBigInteger('toOrganization');
            $table->foreign('toOrganization')->on('organizations')->references('id');

            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->on('products')->references('id');
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->foreign('stock_id')->on('stocks')->references('id');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->on('users')->references('id');
            $table->string('AmvalCode')->nullable();
            $table->string('number');
            $table->string('transferDate')->nullable();
            
            $table->unsignedBigInteger('approveUser');
            $table->foreign('approveUser')->on('users')->references('id');
            $table->boolean('isApproved')->default('0');
            $table->string('approvedNumber')->nullable();

            $table->unsignedBigInteger('denyUser')->nullable();
            $table->foreign('denyUser')->on('users')->references('id');
            $table->boolean('isDenied')->default('0');

            $table->boolean('isRead')->default('0');
            $table->text('description')->nullable();

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
        Schema::dropIfExists('transfers');
    }
};
