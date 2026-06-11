<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('financial_attachments')) {
            Schema::create('financial_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('attachable_type', 180);
                $table->unsignedBigInteger('attachable_id');
                $table->unsignedBigInteger('voucher_id')->nullable();
                $table->string('attachment_kind', 60)->default('document');
                $table->string('disk', 40)->default('public');
                $table->string('file_path', 500);
                $table->string('original_name', 255)->nullable();
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->text('note')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['attachable_type', 'attachable_id'], 'financial_attachments_attachable_index');
                $table->index(['tenant_id', 'attachment_kind'], 'financial_attachments_tenant_kind_index');
                $table->index(['voucher_id'], 'financial_attachments_voucher_index');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback: uploaded financial evidence is audit data.
    }
};
