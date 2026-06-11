<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduledTreasuryNotifications extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('notifs')) {
            Schema::create('notifs', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->string('title', 100);
                $table->text('content');
                $table->boolean('status')->default(false);
                $table->string('source', 80)->nullable()->index();
                $table->string('severity', 30)->nullable();
                $table->string('reference_type', 100)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('alert_key', 191)->nullable()->index();
                $table->date('scheduled_for')->nullable()->index();
                $table->dateTime('sent_at')->nullable();
                $table->timestamps();
                $table->dateTime('deleted_at')->nullable();
            });

            return;
        }

        Schema::table('notifs', function (Blueprint $table) {
            if (!Schema::hasColumn('notifs', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
            }

            if (!Schema::hasColumn('notifs', 'source')) {
                $table->string('source', 80)->nullable()->index();
            }

            if (!Schema::hasColumn('notifs', 'severity')) {
                $table->string('severity', 30)->nullable();
            }

            if (!Schema::hasColumn('notifs', 'reference_type')) {
                $table->string('reference_type', 100)->nullable();
            }

            if (!Schema::hasColumn('notifs', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable();
            }

            if (!Schema::hasColumn('notifs', 'alert_key')) {
                $table->string('alert_key', 191)->nullable()->index();
            }

            if (!Schema::hasColumn('notifs', 'scheduled_for')) {
                $table->date('scheduled_for')->nullable()->index();
            }

            if (!Schema::hasColumn('notifs', 'sent_at')) {
                $table->dateTime('sent_at')->nullable();
            }
        });
    }

    public function down()
    {
        // Non-destructive rollback: notification metadata may already be used by operators.
    }
}
