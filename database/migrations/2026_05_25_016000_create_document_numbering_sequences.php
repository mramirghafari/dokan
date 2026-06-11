<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('document_numbering_sequences')) {
            Schema::create('document_numbering_sequences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('document_type', 60)->index();
                $table->string('prefix', 20);
                $table->string('year', 10)->nullable()->index();
                $table->unsignedBigInteger('next_number')->default(1);
                $table->unsignedTinyInteger('padding')->default(6);
                $table->string('separator', 5)->default('-');
                $table->boolean('reset_yearly')->default(true);
                $table->boolean('isActive')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['tenant_id', 'organization_id', 'document_type', 'prefix', 'year'], 'document_numbering_unique_scope');
            });
        }

        $this->seedDefaultSequences();
    }

    public function down()
    {
        // Non-destructive migration: keep document numbering history intact.
    }

    private function seedDefaultSequences(): void
    {
        $defaults = [
            ['accounting_voucher', 'ACC'],
            ['sales_voucher', 'SAL'],
            ['inventory_receipt', 'INV'],
            ['purchase_order', 'PUR'],
            ['purchase_return', 'PRT'],
            ['purchase_payment', 'PUP'],
            ['treasury_transfer', 'TRF'],
            ['cheque_operation', 'CHK'],
        ];

        foreach ($defaults as [$type, $prefix]) {
            DB::table('document_numbering_sequences')->updateOrInsert(
                ['tenant_id' => null, 'organization_id' => null, 'document_type' => $type, 'prefix' => $prefix, 'year' => null],
                ['next_number' => 1, 'padding' => 6, 'separator' => '-', 'reset_yearly' => 1, 'isActive' => 1, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
};
