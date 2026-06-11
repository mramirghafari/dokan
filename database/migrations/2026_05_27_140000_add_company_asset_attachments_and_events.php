<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('company_asset_attachments')) {
            Schema::create('company_asset_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_asset_id')->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('attachment_type', 60)->default('document')->index();
                $table->string('disk', 40)->default('public');
                $table->string('file_path');
                $table->string('original_name')->nullable();
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('note', 500)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'attachment_type'], 'asset_attachments_tenant_type_index');
            });
        }

        if (!Schema::hasTable('company_asset_events')) {
            Schema::create('company_asset_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_asset_id')->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('event_type', 60)->index();
                $table->date('event_date_en')->nullable()->index();
                $table->string('event_date_fa', 20)->nullable();
                $table->unsignedBigInteger('from_store_id')->nullable()->index();
                $table->unsignedBigInteger('to_store_id')->nullable()->index();
                $table->unsignedBigInteger('from_employee_id')->nullable()->index();
                $table->unsignedBigInteger('to_employee_id')->nullable()->index();
                $table->string('status_before', 40)->nullable();
                $table->string('status_after', 40)->nullable();
                $table->decimal('amount', 18, 2)->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'event_type'], 'asset_events_tenant_type_index');
                $table->index(['company_asset_id', 'event_date_en'], 'asset_events_asset_date_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: asset attachments and events are audit data.
    }
};
