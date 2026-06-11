<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_sales_boards')) {
            Schema::create('crm_sales_boards', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('title', 160);
                $table->string('type', 40)->default('sales_pipeline');
                $table->string('visibility', 30)->default('team');
                $table->text('description')->nullable();
                $table->boolean('is_default')->default(false);
                $table->unsignedInteger('position')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'position'], 'crm_sales_boards_scope_position_index');
                $table->index(['tenant_id', 'type'], 'crm_sales_boards_tenant_type_index');
            });
        }

        if (!Schema::hasTable('crm_sales_board_lists')) {
            Schema::create('crm_sales_board_lists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('board_id');
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('title', 140);
                $table->string('stage_key', 60)->nullable();
                $table->string('color', 20)->default('#7367f0');
                $table->unsignedTinyInteger('probability_percent')->default(0);
                $table->unsignedInteger('wip_limit')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->boolean('is_final')->default(false);
                $table->string('final_status', 30)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['board_id', 'position'], 'crm_sales_lists_board_position_index');
                $table->index(['tenant_id', 'board_id'], 'crm_sales_lists_tenant_board_index');
            });
        }

        if (!Schema::hasTable('crm_sales_board_cards')) {
            Schema::create('crm_sales_board_cards', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('board_id');
                $table->unsignedBigInteger('list_id');
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('opportunity_id')->nullable();
                $table->unsignedBigInteger('assigned_user_id')->nullable();
                $table->string('title', 180);
                $table->text('description')->nullable();
                $table->string('priority', 20)->default('normal');
                $table->string('status', 30)->default('open');
                $table->decimal('amount', 18, 2)->default(0);
                $table->unsignedTinyInteger('probability_percent')->default(0);
                $table->date('expected_close_date_en')->nullable();
                $table->string('expected_close_date_fa', 20)->nullable();
                $table->date('next_action_date_en')->nullable();
                $table->string('next_action_date_fa', 20)->nullable();
                $table->json('labels')->nullable();
                $table->json('checklist')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->timestamp('moved_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'board_id', 'list_id', 'status', 'position'], 'crm_sales_cards_kanban_index');
                $table->index(['assigned_user_id', 'status', 'next_action_date_en'], 'crm_sales_cards_assigned_action_index');
                $table->index(['customer_id', 'status'], 'crm_sales_cards_customer_status_index');
                $table->index(['opportunity_id'], 'crm_sales_cards_opportunity_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep CRM board, list and card history intact.
    }
};
