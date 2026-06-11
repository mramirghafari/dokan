<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->extendTargets();
        $this->extendTargetProducts();
        $this->createCommissionPlans();
        $this->createCommissionPlanTiers();
        $this->createCommissionSettlements();
        $this->createCommissionSettlementLines();
        $this->createManagementReportSnapshots();
    }

    public function down()
    {
        // Non-destructive migration: keep commission and report history intact.
    }

    private function extendTargets(): void
    {
        if (!Schema::hasTable('targets')) {
            return;
        }

        Schema::table('targets', function (Blueprint $table) {
            if (!Schema::hasColumn('targets', 'target_type')) {
                $table->string('target_type')->default('sales_amount')->after('target_price');
            }
            if (!Schema::hasColumn('targets', 'period_type')) {
                $table->string('period_type')->default('custom')->after('target_type');
            }
            if (!Schema::hasColumn('targets', 'calculation_scope')) {
                $table->string('calculation_scope')->default('own_and_children')->after('period_type');
            }
            if (!Schema::hasColumn('targets', 'commission_plan_id')) {
                $table->unsignedBigInteger('commission_plan_id')->nullable()->after('calculation_scope');
            }
            if (!Schema::hasColumn('targets', 'achievement_threshold_percent')) {
                $table->decimal('achievement_threshold_percent', 8, 2)->default(100)->after('commission_plan_id');
            }
            if (!Schema::hasColumn('targets', 'bonus_amount')) {
                $table->decimal('bonus_amount', 18, 2)->default(0)->after('achievement_threshold_percent');
            }
            if (!Schema::hasColumn('targets', 'penalty_amount')) {
                $table->decimal('penalty_amount', 18, 2)->default(0)->after('bonus_amount');
            }
            if (!Schema::hasColumn('targets', 'settlement_status')) {
                $table->string('settlement_status')->default('open')->after('penalty_amount');
            }
            if (!Schema::hasColumn('targets', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('settlement_status');
            }
            if (!Schema::hasColumn('targets', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('targets', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('targets', 'notes')) {
                $table->text('notes')->nullable()->after('closed_at');
            }
        });
    }

    private function extendTargetProducts(): void
    {
        if (!Schema::hasTable('target_products')) {
            return;
        }

        Schema::table('target_products', function (Blueprint $table) {
            if (!Schema::hasColumn('target_products', 'target_type')) {
                $table->string('target_type')->default('quantity')->after('pr_id');
            }
            if (!Schema::hasColumn('target_products', 'weight_percent')) {
                $table->decimal('weight_percent', 8, 2)->default(0)->after('order_price');
            }
            if (!Schema::hasColumn('target_products', 'commission_rate_percent')) {
                $table->decimal('commission_rate_percent', 8, 2)->default(0)->after('weight_percent');
            }
            if (!Schema::hasColumn('target_products', 'commission_amount_per_unit')) {
                $table->decimal('commission_amount_per_unit', 18, 2)->default(0)->after('commission_rate_percent');
            }
            if (!Schema::hasColumn('target_products', 'achievement_count')) {
                $table->decimal('achievement_count', 18, 3)->default(0)->after('commission_amount_per_unit');
            }
            if (!Schema::hasColumn('target_products', 'achievement_amount')) {
                $table->decimal('achievement_amount', 18, 2)->default(0)->after('achievement_count');
            }
            if (!Schema::hasColumn('target_products', 'notes')) {
                $table->text('notes')->nullable()->after('achievement_amount');
            }
        });
    }

    private function createCommissionPlans(): void
    {
        if (Schema::hasTable('commission_plans')) {
            return;
        }

        Schema::create('commission_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->string('code')->nullable()->index();
            $table->string('title');
            $table->string('applies_to_role')->nullable()->index();
            $table->string('calculation_base')->default('invoice_total');
            $table->string('trigger_status')->default('approved');
            $table->string('period_type')->default('monthly');
            $table->boolean('requires_target')->default(false);
            $table->decimal('min_achievement_percent', 8, 2)->default(0);
            $table->decimal('base_rate_percent', 8, 2)->default(0);
            $table->decimal('fixed_amount', 18, 2)->default(0);
            $table->decimal('cap_amount', 18, 2)->nullable();
            $table->boolean('include_tax')->default(true);
            $table->boolean('include_discount')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    private function createCommissionPlanTiers(): void
    {
        if (Schema::hasTable('commission_plan_tiers')) {
            return;
        }

        Schema::create('commission_plan_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commission_plan_id')->index();
            $table->decimal('from_achievement_percent', 8, 2)->default(0);
            $table->decimal('to_achievement_percent', 8, 2)->nullable();
            $table->decimal('rate_percent', 8, 2)->default(0);
            $table->decimal('fixed_bonus_amount', 18, 2)->default(0);
            $table->decimal('amount_per_unit', 18, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    private function createCommissionSettlements(): void
    {
        if (Schema::hasTable('commission_settlements')) {
            return;
        }

        Schema::create('commission_settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('commission_plan_id')->nullable()->index();
            $table->unsignedBigInteger('target_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->dateTime('period_start')->index();
            $table->dateTime('period_end')->index();
            $table->decimal('sales_amount', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('net_amount', 18, 2)->default(0);
            $table->decimal('collected_amount', 18, 2)->default(0);
            $table->decimal('gross_profit_amount', 18, 2)->default(0);
            $table->decimal('target_amount', 18, 2)->default(0);
            $table->decimal('achievement_percent', 8, 2)->default(0);
            $table->decimal('base_commission_amount', 18, 2)->default(0);
            $table->decimal('tier_commission_amount', 18, 2)->default(0);
            $table->decimal('bonus_amount', 18, 2)->default(0);
            $table->decimal('penalty_amount', 18, 2)->default(0);
            $table->decimal('payable_amount', 18, 2)->default(0);
            $table->string('status')->default('calculated')->index();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    private function createCommissionSettlementLines(): void
    {
        if (Schema::hasTable('commission_settlement_lines')) {
            return;
        }

        Schema::create('commission_settlement_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commission_settlement_id')->index();
            $table->unsignedBigInteger('pishfactor_id')->nullable()->index();
            $table->unsignedBigInteger('pish_factor_item_id')->nullable()->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('area_id')->nullable()->index();
            $table->unsignedBigInteger('region_id')->nullable()->index();
            $table->decimal('quantity', 18, 3)->default(0);
            $table->decimal('invoice_amount', 18, 2)->default(0);
            $table->decimal('net_amount', 18, 2)->default(0);
            $table->decimal('calculation_base_amount', 18, 2)->default(0);
            $table->decimal('rate_percent', 8, 2)->default(0);
            $table->decimal('commission_amount', 18, 2)->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    private function createManagementReportSnapshots(): void
    {
        if (Schema::hasTable('management_report_snapshots')) {
            return;
        }

        Schema::create('management_report_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->string('report_key')->index();
            $table->string('title');
            $table->dateTime('period_start')->index();
            $table->dateTime('period_end')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->json('filters')->nullable();
            $table->json('metrics')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};
