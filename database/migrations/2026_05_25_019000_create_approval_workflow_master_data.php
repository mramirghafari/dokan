<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->createApprovalWorkflows();
        $this->createApprovalWorkflowSteps();
        $this->seedDefaults();
    }

    public function down()
    {
        // Non-destructive migration: keep approval workflow settings intact.
    }

    private function createApprovalWorkflows(): void
    {
        if (!Schema::hasTable('approval_workflows')) {
            Schema::create('approval_workflows', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('document_type', 60)->index();
                $table->string('title');
                $table->boolean('is_required')->default(false);
                $table->decimal('amount_threshold', 18, 2)->nullable();
                $table->boolean('isActive')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'organization_id', 'document_type'], 'approval_workflows_scope_doc_index');
            });
        }
    }

    private function createApprovalWorkflowSteps(): void
    {
        if (!Schema::hasTable('approval_workflow_steps')) {
            Schema::create('approval_workflow_steps', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('approval_workflow_id')->index();
                $table->unsignedTinyInteger('step_order')->default(1);
                $table->string('title');
                $table->unsignedBigInteger('role_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->boolean('is_required')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['approval_workflow_id', 'step_order'], 'approval_workflow_steps_order_unique');
            });
        }
    }

    private function seedDefaults(): void
    {
        $documentTypes = [
            'sales_order' => 'تایید سفارش فروش',
            'sales_return' => 'تایید مرجوعی فروش',
            'discount' => 'تایید تخفیف',
            'settlement' => 'تایید تسویه',
            'warehouse_issue' => 'تایید خروج کالا',
            'purchase_order' => 'تایید سفارش خرید',
        ];

        foreach ($documentTypes as $documentType => $title) {
            DB::table('approval_workflows')->updateOrInsert(
                ['tenant_id' => null, 'organization_id' => null, 'document_type' => $documentType],
                ['title' => $title, 'is_required' => false, 'isActive' => 1, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
};
