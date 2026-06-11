<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('management_report_templates')) {
            Schema::create('management_report_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('template_key', 80)->index();
                $table->string('title', 190);
                $table->json('sections_json')->nullable();
                $table->json('filters_json')->nullable();
                $table->json('chart_settings_json')->nullable();
                $table->string('default_export_format', 20)->default('excel');
                $table->boolean('is_shared')->default(false)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'template_key', 'is_active'], 'management_templates_lookup_index');
            });
        }

        if (!Schema::hasTable('management_report_schedules')) {
            Schema::create('management_report_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('management_report_template_id')->nullable()->index();
                $table->string('title', 190);
                $table->string('frequency', 30)->default('monthly')->index();
                $table->string('delivery_format', 20)->default('excel');
                $table->json('recipients_json')->nullable();
                $table->json('filters_json')->nullable();
                $table->timestamp('next_run_at')->nullable()->index();
                $table->timestamp('last_run_at')->nullable();
                $table->string('last_status', 30)->nullable()->index();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'frequency', 'is_active'], 'management_schedules_lookup_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: keep dashboard templates and schedule audit data intact.
    }
};
