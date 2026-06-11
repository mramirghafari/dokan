<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('receipts')) {
            return;
        }

        Schema::table('receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('receipts', 'scale_ticket_number')) {
                $table->string('scale_ticket_number', 80)->nullable()->after('driver')->index();
            }
            if (!Schema::hasColumn('receipts', 'vehicle_plate')) {
                $table->string('vehicle_plate', 50)->nullable()->after('scale_ticket_number')->index();
            }
            if (!Schema::hasColumn('receipts', 'waybill_number')) {
                $table->string('waybill_number', 80)->nullable()->after('vehicle_plate')->index();
            }
            if (!Schema::hasColumn('receipts', 'gross_weight')) {
                $table->decimal('gross_weight', 18, 3)->nullable()->after('waybill_number');
            }
            if (!Schema::hasColumn('receipts', 'tare_weight')) {
                $table->decimal('tare_weight', 18, 3)->nullable()->after('gross_weight');
            }
            if (!Schema::hasColumn('receipts', 'net_weight')) {
                $table->decimal('net_weight', 18, 3)->nullable()->after('tare_weight');
            }
            if (!Schema::hasColumn('receipts', 'weighing_notes')) {
                $table->text('weighing_notes')->nullable()->after('net_weight');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive migration: keep weighbridge audit data intact.
    }
};
