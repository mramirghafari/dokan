<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bi_report_schedules')) {
            Schema::create('bi_report_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->unsignedBigInteger('bi_report_template_id')->nullable();
                $table->string('title', 180);
                $table->string('frequency', 30)->default('daily');
                $table->string('delivery_format', 30)->default('csv');
                $table->json('recipients')->nullable();
                $table->json('channels')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->timestamp('last_run_at')->nullable();
                $table->string('last_status', 30)->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'is_active'], 'bi_report_schedules_scope_active_index');
                $table->index(['next_run_at', 'is_active'], 'bi_report_schedules_due_index');
            });
        }

        if (!Schema::hasTable('bi_report_deliveries')) {
            Schema::create('bi_report_deliveries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bi_report_schedule_id')->nullable();
                $table->unsignedBigInteger('bi_report_template_id')->nullable();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('delivery_token', 80)->unique();
                $table->string('title', 180);
                $table->string('dataset_key', 100);
                $table->string('delivery_format', 30)->default('csv');
                $table->unsignedInteger('recipient_count')->default(0);
                $table->json('channels')->nullable();
                $table->json('filters')->nullable();
                $table->json('output_snapshot')->nullable();
                $table->unsignedInteger('row_count')->default(0);
                $table->string('status', 30)->default('success');
                $table->text('message')->nullable();
                $table->unsignedBigInteger('generated_by')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'organization_id', 'generated_at'], 'bi_report_deliveries_scope_generated_index');
                $table->index(['bi_report_schedule_id', 'status'], 'bi_report_deliveries_schedule_status_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bi_report_deliveries');
        Schema::dropIfExists('bi_report_schedules');
    }
};
