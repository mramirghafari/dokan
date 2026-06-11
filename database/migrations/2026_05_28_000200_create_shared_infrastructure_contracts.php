<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendLogsTable();
        $this->createDataExchangeRunsTable();
    }

    public function down(): void
    {
        // Non-destructive rollback: shared audit and exchange traces are operational evidence.
    }

    private function extendLogsTable(): void
    {
        if (!Schema::hasTable('logs')) {
            return;
        }

        Schema::table('logs', function (Blueprint $table) {
            if (!Schema::hasColumn('logs', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('user_id')->index();
            }

            if (!Schema::hasColumn('logs', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('tenant_id')->index();
            }

            if (!Schema::hasColumn('logs', 'section')) {
                $table->string('section', 120)->nullable()->after('organization_id')->index();
            }

            if (!Schema::hasColumn('logs', 'section_id')) {
                $table->unsignedBigInteger('section_id')->nullable()->after('section')->index();
            }

            if (!Schema::hasColumn('logs', 'event_key')) {
                $table->string('event_key', 120)->nullable()->after('action')->index();
            }

            if (!Schema::hasColumn('logs', 'source_type')) {
                $table->string('source_type', 180)->nullable()->after('event_key');
            }

            if (!Schema::hasColumn('logs', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }

            if (!Schema::hasColumn('logs', 'payload_json')) {
                $table->json('payload_json')->nullable()->after('description');
            }
        });
    }

    private function createDataExchangeRunsTable(): void
    {
        if (Schema::hasTable('data_exchange_runs')) {
            return;
        }

        Schema::create('data_exchange_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('store_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('direction', 20)->index();
            $table->string('entity_type', 80)->index();
            $table->string('file_name')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('options_json')->nullable();
            $table->json('summary_json')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'direction', 'entity_type'], 'data_exchange_scope_index');
        });
    }
};
