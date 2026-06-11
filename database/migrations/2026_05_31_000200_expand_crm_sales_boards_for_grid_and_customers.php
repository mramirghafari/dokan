<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('crm_sales_boards')) {
            Schema::table('crm_sales_boards', function (Blueprint $table) {
                if (!Schema::hasColumn('crm_sales_boards', 'owner_user_id')) {
                    $table->unsignedBigInteger('owner_user_id')->nullable()->after('organization_id');
                    $table->index(['owner_user_id', 'tenant_id'], 'crm_sales_boards_owner_tenant_index');
                }

                if (!Schema::hasColumn('crm_sales_boards', 'cover_image_path')) {
                    $table->string('cover_image_path')->nullable()->after('description');
                }
            });
        }

        if (Schema::hasTable('crm_sales_board_cards')) {
            Schema::table('crm_sales_board_cards', function (Blueprint $table) {
                if (!Schema::hasColumn('crm_sales_board_cards', 'card_type')) {
                    $table->string('card_type', 30)->default('task')->after('assigned_user_id');
                    $table->index(['tenant_id', 'card_type', 'status'], 'crm_sales_cards_type_status_index');
                }

                if (!Schema::hasColumn('crm_sales_board_cards', 'source_filter')) {
                    $table->json('source_filter')->nullable()->after('checklist');
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep enhanced CRM board metadata intact.
    }
};
