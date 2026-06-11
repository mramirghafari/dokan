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
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('description');
                $table->boolean('isActive')->default(1);
                $table->softDeletes();
                $table->timestamps();
            });

            Schema::create('permission_user', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->foreign('permission_id')->on('permissions')->references('id')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->on('users')->references('id')->cascadeOnDelete();
                $table->primary(['permission_id','user_id']);
            });

            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('description');
                $table->boolean('isActive')->default(1);
                $table->softDeletes();
                $table->timestamps();
            });

            Schema::create('permission_role', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->foreign('permission_id')->on('permissions')->references('id')->cascadeOnDelete();
                $table->unsignedBigInteger('role_id');
                $table->foreign('role_id')->on('roles')->references('id')->cascadeOnDelete();
                $table->primary(['permission_id','role_id']);
            });

            Schema::create('role_user', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->foreign('role_id')->on('roles')->references('id')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->on('users')->references('id')->cascadeOnDelete();
                $table->primary(['role_id','user_id']);
            });

            Schema::create('role_store', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->foreign('role_id')->on('roles')->references('id')->cascadeOnDelete();
                $table->unsignedBigInteger('store_id');
                $table->foreign('store_id')->on('stores')->references('id')->cascadeOnDelete();
                $table->primary(['role_id','store_id']);
            });

        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('role_store');
            Schema::dropIfExists('permission_user');
            Schema::dropIfExists('permission_role');
            Schema::dropIfExists('role_user');
            Schema::dropIfExists('permissions');
            Schema::dropIfExists('roles');
        }
};
