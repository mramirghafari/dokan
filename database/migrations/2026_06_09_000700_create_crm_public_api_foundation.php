<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_public_api_clients')) {
            Schema::create('crm_public_api_clients', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('code', 80)->unique();
                $table->string('title', 180);
                $table->string('token_hash', 128);
                $table->json('scopes')->nullable();
                $table->string('allowed_ips', 500)->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('request_count')->default(0);
                $table->timestamp('last_used_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'is_active'], 'crm_public_api_clients_scope_active_idx');
            });
        }

        if (!Schema::hasTable('crm_public_api_request_logs')) {
            Schema::create('crm_public_api_request_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('crm_public_api_client_id')->nullable();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('endpoint', 120);
                $table->string('method', 12)->default('POST');
                $table->string('external_id', 160)->nullable();
                $table->string('status', 40)->default('processed');
                $table->string('ip_address', 80)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_type', 120)->nullable();
                $table->json('payload_snapshot')->nullable();
                $table->text('message')->nullable();
                $table->timestamps();

                $table->index(['crm_public_api_client_id', 'endpoint', 'created_at'], 'crm_public_api_logs_client_endpoint_idx');
                $table->index(['tenant_id', 'organization_id', 'status'], 'crm_public_api_logs_scope_status_idx');
                $table->index(['external_id', 'endpoint'], 'crm_public_api_logs_external_endpoint_idx');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive: public CRM API clients and request logs are audit data.
    }
};
