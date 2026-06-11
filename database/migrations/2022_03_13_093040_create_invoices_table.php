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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->on('users')->references('id');
            $table->string('invoiceID');
            $table->string('shopName');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('buyDate')->nullable();
            $table->string('inputDate')->nullable();
            $table->text('description')->nullable();
            $table->text('file')->nullable();
            $table->string('price')->nullable();
            $table->timestamps();
        });
        Schema::create('details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->on('invoices')->references('id')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->on('products')->references('id');
            $table->string('garanty')->nullable();
            $table->string('number');
            $table->string('price');
            $table->string('totalPrice');
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
        Schema::dropIfExists('details');
        Schema::dropIfExists('invoices');
    }
};
