<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->createCustomerSegments();
        $this->extendCustomers();
        $this->backfillCustomerSegments();
    }

    public function down()
    {
        // Non-destructive migration: keep standardized customer master-data intact.
    }

    private function createCustomerSegments(): void
    {
        if (Schema::hasTable('customer_segments')) {
            return;
        }

        Schema::create('customer_segments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->string('type', 30)->index();
            $table->string('title');
            $table->string('code', 60)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('isActive')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'organization_id', 'type'], 'customer_segments_scope_type_index');
        });
    }

    private function extendCustomers(): void
    {
        if (!Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'customer_group_id')) {
                $table->unsignedBigInteger('customer_group_id')->nullable()->after('senf');
            }
            if (!Schema::hasColumn('customers', 'sales_channel_id')) {
                $table->unsignedBigInteger('sales_channel_id')->nullable()->after('channel');
            }
            if (!Schema::hasColumn('customers', 'customer_status_id')) {
                $table->unsignedBigInteger('customer_status_id')->nullable()->after('status');
            }
        });

        $this->addIndexIfMissing('customers', 'customer_group_id', 'customers_customer_group_id_index');
        $this->addIndexIfMissing('customers', 'sales_channel_id', 'customers_sales_channel_id_index');
        $this->addIndexIfMissing('customers', 'customer_status_id', 'customers_customer_status_id_index');
    }

    private function backfillCustomerSegments(): void
    {
        if (!Schema::hasTable('customers') || !Schema::hasTable('customer_segments')) {
            return;
        }

        DB::table('customers')
            ->select('id', 'tenant_id', 'organization_id', 'senf', 'channel', 'status')
            ->orderBy('id')
            ->chunkById(500, function ($customers) {
                foreach ($customers as $customer) {
                    $organizationId = $this->primaryOrganizationId($customer->organization_id);
                    $tenantId = $customer->tenant_id ?: $this->tenantIdForOrganization($organizationId);
                    $groupId = $this->segmentId('customer_group', $customer->senf, $organizationId, $tenantId);
                    $channelId = $this->segmentId('sales_channel', $customer->channel, $organizationId, $tenantId);
                    $statusTitle = $this->statusTitle($customer->status);
                    $statusId = $this->segmentId('customer_status', $statusTitle, $organizationId, $tenantId, true);

                    DB::table('customers')->where('id', $customer->id)->update([
                        'customer_group_id' => $groupId,
                        'sales_channel_id' => $channelId,
                        'customer_status_id' => $statusId,
                    ]);
                }
            });
    }

    private function segmentId(string $type, $title, ?int $organizationId, $tenantId, bool $isDefault = false): ?int
    {
        $title = trim((string) $title);
        if ($title === '' || $title === '0') {
            return null;
        }

        $existingId = DB::table('customer_segments')
            ->where('type', $type)
            ->where('title', $title)
            ->where(function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId)->orWhereNull('organization_id');
            })
            ->value('id');

        if ($existingId) {
            return (int) $existingId;
        }

        return (int) DB::table('customer_segments')->insertGetId([
            'tenant_id' => $tenantId,
            'organization_id' => $organizationId,
            'type' => $type,
            'title' => $title,
            'code' => $this->code($type, $title),
            'sort_order' => 0,
            'is_default' => $isDefault,
            'isActive' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function statusTitle($status): string
    {
        if (is_numeric($status)) {
            return (int) $status === 1 ? 'فعال' : 'غیرفعال';
        }

        $status = trim((string) $status);
        return $status !== '' ? $status : 'فعال';
    }

    private function primaryOrganizationId($raw): ?int
    {
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $values = is_array($decoded) ? $decoded : [$raw];

        foreach ($values as $value) {
            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return null;
    }

    private function tenantIdForOrganization(?int $organizationId): ?int
    {
        if (!$organizationId || !Schema::hasTable('organizations')) {
            return null;
        }

        $organization = DB::table('organizations')->where('id', $organizationId)->first();
        if (!$organization) {
            return null;
        }

        return $organization->tenant_id ?? $organization->tenants_id ?? null;
    }

    private function code(string $type, string $title): string
    {
        return substr($type . '_' . md5($title), 0, 60);
    }

    private function addIndexIfMissing(string $tableName, string $columnName, string $indexName): void
    {
        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();

        if (!$exists && Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $table) use ($columnName, $indexName) {
                $table->index($columnName, $indexName);
            });
        }
    }
};
