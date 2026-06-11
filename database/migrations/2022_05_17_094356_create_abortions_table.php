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
        Schema::create('abortions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->on('users')->references('id');
            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')->on('brands')->references('id');
            $table->unsignedBigInteger('parentCategory_id');
            $table->foreign('parentCategory_id')->on('categories')->references('id');
            $table->unsignedBigInteger('childCategory_id')->nullable();
            $table->foreign('childCategory_id')->on('categories')->references('id');
            $table->unsignedBigInteger('organization_id');
            $table->foreign('organization_id')->on('organizations')->references('id');
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->on('stores')->references('id');
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->on('employees')->references('id');
            $table->string('inputDate');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('entity')->default(0);
            $table->boolean('isActive')->default(1);
            $table->softDeletes();
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
        Schema::dropIfExists('abortions');
    }
};
