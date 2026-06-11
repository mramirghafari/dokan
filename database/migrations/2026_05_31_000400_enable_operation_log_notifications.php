<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')
            ->where(function ($query) {
                $query->where('key', 'notification_events_enabled')
                    ->orWhere('title', 'notification_events_enabled');
            })
            ->orderBy('id')
            ->chunkById(100, function ($settings) {
                foreach ($settings as $setting) {
                    $events = json_decode((string) $setting->value, true);

                    if (!is_array($events)) {
                        $events = array_filter([(string) $setting->value]);
                    }

                    if (!in_array('system_operation_logged', $events, true)) {
                        $events[] = 'system_operation_logged';

                        DB::table('settings')->where('id', $setting->id)->update([
                            'value' => json_encode(array_values($events)),
                            'type' => 'multiselect',
                            'category' => 'notification_sms',
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Non-destructive migration: keep notification choices selected by operators.
    }
};
