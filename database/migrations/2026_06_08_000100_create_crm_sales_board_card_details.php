<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_sales_board_card_checklist_items')) {
            Schema::create('crm_sales_board_card_checklist_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('card_id');
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('title', 220);
                $table->boolean('is_done')->default(false);
                $table->timestamp('done_at')->nullable();
                $table->unsignedBigInteger('done_by')->nullable();
                $table->unsignedInteger('position')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['card_id', 'position'], 'crm_card_checklist_card_position_index');
                $table->index(['tenant_id', 'organization_id', 'is_done'], 'crm_card_checklist_scope_status_index');
            });
        }

        if (!Schema::hasTable('crm_sales_board_card_comments')) {
            Schema::create('crm_sales_board_card_comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('card_id');
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->text('comment');
                $table->json('mentions')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['card_id', 'created_at'], 'crm_card_comments_card_created_index');
                $table->index(['tenant_id', 'organization_id'], 'crm_card_comments_scope_index');
            });
        }

        if (!Schema::hasTable('crm_sales_board_card_attachments')) {
            Schema::create('crm_sales_board_card_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('card_id');
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('disk', 40)->default('public');
                $table->string('path');
                $table->string('original_name');
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['card_id', 'created_at'], 'crm_card_attachments_card_created_index');
                $table->index(['tenant_id', 'organization_id'], 'crm_card_attachments_scope_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep CRM card operational history intact.
    }
};
