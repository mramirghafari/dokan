<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_automation_rules')) {
            Schema::create('crm_automation_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('board_id');
                $table->unsignedBigInteger('list_id')->nullable();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('trigger_event', 60)->default('card_moved_to_list');
                $table->string('card_type', 30)->nullable();
                $table->string('action_type', 60)->default('create_followup');
                $table->unsignedBigInteger('assigned_user_id')->nullable();
                $table->unsignedSmallInteger('due_days')->default(1);
                $table->string('priority', 20)->default('normal');
                $table->string('title_template', 220);
                $table->text('description_template')->nullable();
                $table->boolean('notify_assignee')->default(true);
                $table->boolean('notify_board_owner')->default(true);
                $table->boolean('escalate_to_manager')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('execution_count')->default(0);
                $table->timestamp('last_executed_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['board_id', 'list_id', 'trigger_event', 'is_active'], 'crm_automation_rules_trigger_index');
                $table->index(['tenant_id', 'organization_id', 'is_active'], 'crm_automation_rules_scope_index');
            });
        }

        if (Schema::hasTable('crm_followups')) {
            Schema::table('crm_followups', function (Blueprint $table) {
                if (!Schema::hasColumn('crm_followups', 'source_type')) {
                    $table->string('source_type', 120)->nullable()->after('status');
                }

                if (!Schema::hasColumn('crm_followups', 'source_id')) {
                    $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
                }

                if (!Schema::hasColumn('crm_followups', 'automation_rule_id')) {
                    $table->unsignedBigInteger('automation_rule_id')->nullable()->after('source_id');
                }
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->where(function ($query) {
                    $query->where('key', 'crm_automation_policy')->orWhere('title', 'crm_automation_policy');
                })
                ->orderBy('id')
                ->chunkById(100, function ($settings) {
                    foreach ($settings as $setting) {
                        $value = json_decode((string) $setting->value, true);

                        if (!is_array($value)) {
                            $value = array_filter([(string) $setting->value]);
                        }

                        if (!in_array('task_after_card_move', $value, true)) {
                            $value[] = 'task_after_card_move';
                            DB::table('settings')->where('id', $setting->id)->update([
                                'value' => json_encode(array_values($value)),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        // Non-destructive migration: keep CRM automation rules and generated follow-up trace intact.
    }
};
