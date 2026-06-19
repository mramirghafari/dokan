<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("product_price_periods", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("tenant_id")->nullable()->index();
            $table->foreignId("product_id")->constrained("products")->cascadeOnDelete();
            $table->string("price_type", 32)->index();
            $table->decimal("amount", 18, 2);
            $table->date("starts_at")->nullable()->index();
            $table->date("ends_at")->nullable()->index();
            $table->string("starts_at_fa", 16)->nullable();
            $table->string("ends_at_fa", 16)->nullable();
            $table->unsignedInteger("priority")->default(0);
            $table->boolean("status")->default(true);
            $table->json("metadata")->nullable();
            $table->timestamps();

            $table->index(["product_id", "price_type", "status"], "ppp_product_type_status_idx");
            $table->index(["product_id", "price_type", "starts_at"], "ppp_product_type_start_idx");
            $table->index(["product_id", "price_type", "ends_at"], "ppp_product_type_end_idx");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("product_price_periods");
    }
};

