<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employees')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'national_code')) {
                $table->string('national_code', 20)->nullable()->after('personalID');
            }
            if (!Schema::hasColumn('employees', 'personnel_code')) {
                $table->string('personnel_code', 50)->nullable()->after('national_code');
            }
            if (!Schema::hasColumn('employees', 'father_name')) {
                $table->string('father_name', 120)->nullable()->after('personnel_code');
            }
            if (!Schema::hasColumn('employees', 'mobile')) {
                $table->string('mobile', 30)->nullable()->after('father_name');
            }
            if (!Schema::hasColumn('employees', 'job_title')) {
                $table->string('job_title', 150)->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('employees', 'employment_type')) {
                // official | contractual | daily | hourly
                $table->string('employment_type', 30)->nullable()->after('job_title');
            }
            if (!Schema::hasColumn('employees', 'hire_date_en')) {
                $table->date('hire_date_en')->nullable()->after('employment_type');
            }
            if (!Schema::hasColumn('employees', 'hire_date_fa')) {
                $table->string('hire_date_fa', 20)->nullable()->after('hire_date_en');
            }
            if (!Schema::hasColumn('employees', 'insurance_number')) {
                $table->string('insurance_number', 40)->nullable()->after('hire_date_fa');
            }
            if (!Schema::hasColumn('employees', 'bank_name')) {
                $table->string('bank_name', 120)->nullable()->after('insurance_number');
            }
            if (!Schema::hasColumn('employees', 'bank_account')) {
                $table->string('bank_account', 60)->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('employees', 'sheba')) {
                $table->string('sheba', 40)->nullable()->after('bank_account');
            }
            if (!Schema::hasColumn('employees', 'marital_status')) {
                // single | married
                $table->string('marital_status', 20)->nullable()->after('sheba');
            }
            if (!Schema::hasColumn('employees', 'children_count')) {
                $table->unsignedTinyInteger('children_count')->default(0)->after('marital_status');
            }
            if (!Schema::hasColumn('employees', 'military_status')) {
                // done | exempt | not_required
                $table->string('military_status', 30)->nullable()->after('children_count');
            }
            if (!Schema::hasColumn('employees', 'employment_status')) {
                // active | suspended | terminated
                $table->string('employment_status', 30)->default('active')->after('military_status');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive: keep personnel profile columns to preserve HR data integrity.
    }
};
