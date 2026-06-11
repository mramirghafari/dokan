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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('organization_id');
            $table->foreign('organization_id')->on('organizations')->references('id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->on('units')->references('id');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('units');
    }
};
