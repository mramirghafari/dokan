<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products') || Schema::hasColumn('products', 'order_quantity_mode')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('order_quantity_mode', 20)->nullable()->after('pack_sale_status');
        });

        DB::table('products')->orderBy('id')->chunkById(200, function ($products) {
            foreach ($products as $product) {
                $pack = (bool) $product->pack_sale_status;
                $item = (bool) $product->item_sale_status;

                $mode = match (true) {
                    $pack && $item => 'both',
                    $pack => 'secondary_unit',
                    $item => 'main_unit',
                    default => 'none',
                };

                DB::table('products')->where('id', $product->id)->update([
                    'order_quantity_mode' => $mode,
                ]);
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'order_quantity_mode')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('order_quantity_mode');
            });
        }
    }
};
