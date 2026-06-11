<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_integration_connections')) {
            Schema::create('crm_integration_connections', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('type', 40);
                $table->string('provider', 80)->default('generic');
                $table->string('title', 180);
                $table->string('endpoint_url', 500)->nullable();
                $table->string('webhook_secret_hash', 128)->nullable();
                $table->json('settings')->nullable();
                $table->json('scopes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_synced_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'type', 'is_active'], 'crm_integration_connections_scope_type_idx');
            });
        }

        if (!Schema::hasTable('crm_integration_sync_logs')) {
            Schema::create('crm_integration_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('crm_integration_connection_id')->nullable();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('integration_type', 40);
                $table->string('provider', 80)->default('generic');
                $table->string('direction', 20)->default('outbound');
                $table->string('operation', 80);
                $table->string('status', 40)->default('queued');
                $table->string('external_id', 180)->nullable();
                $table->string('syncable_type', 160)->nullable();
                $table->unsignedBigInteger('syncable_id')->nullable();
                $table->json('payload_snapshot')->nullable();
                $table->json('response_snapshot')->nullable();
                $table->text('message')->nullable();
                $table->timestamp('attempted_at')->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['crm_integration_connection_id', 'operation', 'status'], 'crm_integration_logs_connection_operation_idx');
                $table->index(['tenant_id', 'organization_id', 'integration_type', 'status'], 'crm_integration_logs_scope_type_idx');
                $table->index(['syncable_type', 'syncable_id', 'operation'], 'crm_integration_logs_syncable_idx');
                $table->index(['external_id', 'integration_type', 'provider'], 'crm_integration_logs_external_idx');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive: integration connection and sync history are audit data.
    }
};
