<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_collaboration_comments')) {
            Schema::create('crm_collaboration_comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('commentable_type', 160);
                $table->unsignedBigInteger('commentable_id');
                $table->unsignedBigInteger('user_id');
                $table->text('body');
                $table->json('mentioned_user_ids')->nullable();
                $table->string('visibility', 30)->default('team');
                $table->boolean('is_pinned')->default(false);
                $table->string('source', 40)->default('workbench');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'commentable_type', 'commentable_id'], 'crm_comments_tenant_target_index');
                $table->index(['organization_id', 'created_at'], 'crm_comments_org_created_index');
                $table->index(['user_id', 'created_at'], 'crm_comments_user_created_index');
            });
        }

        if (!Schema::hasTable('crm_collaboration_mentions')) {
            Schema::create('crm_collaboration_mentions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('comment_id');
                $table->unsignedBigInteger('mentioned_user_id');
                $table->unsignedBigInteger('mentioned_by_user_id')->nullable();
                $table->string('mentionable_type', 160);
                $table->unsignedBigInteger('mentionable_id');
                $table->timestamp('notified_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['comment_id', 'mentioned_user_id'], 'crm_mentions_comment_user_unique');
                $table->index(['mentioned_user_id', 'read_at', 'created_at'], 'crm_mentions_user_read_created_index');
                $table->index(['tenant_id', 'mentionable_type', 'mentionable_id'], 'crm_mentions_tenant_target_index');
            });
        }

        if (!Schema::hasTable('crm_workbench_preferences')) {
            Schema::create('crm_workbench_preferences', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->string('focus_scope', 30)->default('mine');
                $table->json('enabled_widgets')->nullable();
                $table->json('filters')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique('user_id', 'crm_workbench_preferences_user_unique');
                $table->index(['tenant_id', 'focus_scope'], 'crm_workbench_preferences_tenant_scope_index');
                $table->index(['organization_id', 'focus_scope'], 'crm_workbench_preferences_org_scope_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep CRM collaboration history and user workbench preferences intact.
    }
};
