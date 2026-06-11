<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_health_snapshots')) {
            Schema::create('crm_health_snapshots', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('tenant_id')->nullable();
                $table->unsignedInteger('organization_id')->nullable();
                $table->string('scope_label', 120)->default('global');
                $table->unsignedTinyInteger('health_score')->default(100);
                $table->string('risk_level', 30)->default('low');
                $table->json('summary')->nullable();
                $table->json('issues')->nullable();
                $table->json('recommendations')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->unsignedBigInteger('generated_by')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'organization_id', 'generated_at'], 'crm_health_snapshots_scope_generated_idx');
                $table->index(['risk_level', 'health_score'], 'crm_health_snapshots_risk_score_idx');
            });
        }
    }

    public function down(): void
    {
        // Non-destructive: CRM health snapshots are audit evidence.
    }
};
