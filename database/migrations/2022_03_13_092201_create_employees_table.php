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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('personalID')->nullable();
            $table->unsignedBigInteger('parentUnit_id')->nullable();
            $table->foreign('parentUnit_id')->on('units')->references('id');
            $table->unsignedBigInteger('childUnit_id')->nullable();
            $table->foreign('childUnit_id')->on('units')->references('id');
            $table->unsignedBigInteger('organization_id');
            $table->foreign('organization_id')->on('organizations')->references('id');
            $table->boolean('isActive')->default(true);
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
        Schema::dropIfExists('employees');
    }
};
