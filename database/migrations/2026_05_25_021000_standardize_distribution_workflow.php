<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $this->extendShipments();
        $this->extendShipmentRoutes();
        $this->createShipmentEvents();
        $this->backfillStatuses();
    }

    public function down()
    {
        // Non-destructive migration: keep distribution workflow trace intact.
    }

    private function extendShipments(): void
    {
        if (!Schema::hasTable('shipments')) {
            return;
        }

        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('shipments', 'shipment_status')) {
                $table->string('shipment_status', 30)->default('planned')->after('status');
            }
            if (!Schema::hasColumn('shipments', 'loading_status')) {
                $table->string('loading_status', 30)->default('pending')->after('shipment_status');
            }
            if (!Schema::hasColumn('shipments', 'total_orders')) {
                $table->unsignedInteger('total_orders')->default(0)->after('loading_status');
            }
            if (!Schema::hasColumn('shipments', 'total_packs')) {
                $table->decimal('total_packs', 18, 3)->default(0)->after('total_orders');
            }
            if (!Schema::hasColumn('shipments', 'vehicle_capacity')) {
                $table->decimal('vehicle_capacity', 18, 3)->default(0)->after('total_packs');
            }
            if (!Schema::hasColumn('shipments', 'loaded_at')) {
                $table->timestamp('loaded_at')->nullable()->after('vehicle_capacity');
            }
            if (!Schema::hasColumn('shipments', 'departed_at')) {
                $table->timestamp('departed_at')->nullable()->after('loaded_at');
            }
            if (!Schema::hasColumn('shipments', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('departed_at');
            }
        });

        $this->addIndexIfMissing('shipments', ['tenant_id', 'shipment_status'], 'shipments_tenant_status_index');
        $this->addIndexIfMissing('shipments', ['driver_id', 'shipment_status'], 'shipments_driver_status_index');
    }

    private function extendShipmentRoutes(): void
    {
        if (!Schema::hasTable('shipment_routes')) {
            return;
        }

        Schema::table('shipment_routes', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_routes', 'stop_type')) {
                $table->string('stop_type', 30)->default('delivery')->after('factor_id');
            }
            if (!Schema::hasColumn('shipment_routes', 'delivery_status')) {
                $table->string('delivery_status', 30)->default('planned')->after('status');
            }
            if (!Schema::hasColumn('shipment_routes', 'planned_packs')) {
                $table->decimal('planned_packs', 18, 3)->default(0)->after('delivery_status');
            }
            if (!Schema::hasColumn('shipment_routes', 'delivered_packs')) {
                $table->decimal('delivered_packs', 18, 3)->default(0)->after('planned_packs');
            }
            if (!Schema::hasColumn('shipment_routes', 'arrived_at')) {
                $table->timestamp('arrived_at')->nullable()->after('delivered_packs');
            }
            if (!Schema::hasColumn('shipment_routes', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('arrived_at');
            }
            if (!Schema::hasColumn('shipment_routes', 'failed_reason')) {
                $table->string('failed_reason', 500)->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('shipment_routes', 'receiver_name')) {
                $table->string('receiver_name')->nullable()->after('failed_reason');
            }
            if (!Schema::hasColumn('shipment_routes', 'signature_path')) {
                $table->string('signature_path')->nullable()->after('receiver_name');
            }
        });

        $this->addIndexIfMissing('shipment_routes', ['shipment_id', 'delivery_status'], 'shipment_routes_shipment_delivery_index');
        $this->addIndexIfMissing('shipment_routes', ['factor_id'], 'shipment_routes_factor_index');
    }

    private function createShipmentEvents(): void
    {
        if (!Schema::hasTable('shipment_events')) {
            Schema::create('shipment_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedInteger('shipment_id')->index();
                $table->unsignedBigInteger('shipment_route_id')->nullable()->index();
                $table->string('event_type', 40)->index();
                $table->string('from_status', 30)->nullable();
                $table->string('to_status', 30)->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    private function backfillStatuses(): void
    {
        if (Schema::hasTable('shipments') && Schema::hasColumn('shipments', 'shipment_status')) {
            DB::table('shipments')->orderBy('id')->chunkById(200, function ($shipments) {
                foreach ($shipments as $shipment) {
                    DB::table('shipments')->where('id', $shipment->id)->update([
                        'shipment_status' => (int) $shipment->status === 1 ? 'completed' : 'planned',
                        'loading_status' => (int) $shipment->status === 1 ? 'closed' : 'pending',
                    ]);
                }
            });
        }

        if (Schema::hasTable('shipment_routes') && Schema::hasColumn('shipment_routes', 'delivery_status')) {
            DB::table('shipment_routes')->orderBy('id')->chunkById(200, function ($routes) {
                foreach ($routes as $route) {
                    DB::table('shipment_routes')->where('id', $route->id)->update([
                        'delivery_status' => (int) $route->status === 1 ? 'delivered' : 'planned',
                    ]);
                }
            });
        }
    }

    private function addIndexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        $exists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();

        if (!$exists) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }
};
