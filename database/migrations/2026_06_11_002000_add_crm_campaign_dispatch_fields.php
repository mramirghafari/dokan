<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('crm_campaigns')) {
            Schema::table('crm_campaigns', function (Blueprint $table) {
                if (!Schema::hasColumn('crm_campaigns', 'dispatch_status')) {
                    $table->string('dispatch_status', 30)->default('idle')->index()->after('status');
                }
                if (!Schema::hasColumn('crm_campaigns', 'dispatched_at')) {
                    $table->timestamp('dispatched_at')->nullable()->after('dispatch_status');
                }
                if (!Schema::hasColumn('crm_campaigns', 'failed_send_count')) {
                    $table->unsignedInteger('failed_send_count')->default(0)->after('sent_count');
                }
            });
        }

        if (Schema::hasTable('crm_campaign_audiences')) {
            Schema::table('crm_campaign_audiences', function (Blueprint $table) {
                if (!Schema::hasColumn('crm_campaign_audiences', 'sms_status')) {
                    $table->string('sms_status', 30)->nullable()->index()->after('status');
                }
                if (!Schema::hasColumn('crm_campaign_audiences', 'sms_error')) {
                    $table->string('sms_error', 500)->nullable()->after('sms_status');
                }
                if (!Schema::hasColumn('crm_campaign_audiences', 'provider_message_id')) {
                    $table->string('provider_message_id', 120)->nullable()->after('sms_error');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crm_campaigns')) {
            Schema::table('crm_campaigns', function (Blueprint $table) {
                foreach (['dispatch_status', 'dispatched_at', 'failed_send_count'] as $column) {
                    if (Schema::hasColumn('crm_campaigns', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('crm_campaign_audiences')) {
            Schema::table('crm_campaign_audiences', function (Blueprint $table) {
                foreach (['sms_status', 'sms_error', 'provider_message_id'] as $column) {
                    if (Schema::hasColumn('crm_campaign_audiences', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
