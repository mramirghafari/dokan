<?php

namespace App\Services;

use App\Models\ShipmentEvent;
use App\Models\ShipmentRoute;
use App\Models\Shipments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class DistributionWorkflowService
{
    public function markLoaded(Shipments $shipment, $user = null): Shipments
    {
        return $this->transitionShipment($shipment, [
            'loading_status' => 'loaded',
            'shipment_status' => 'loaded',
            'loaded_at' => now(),
        ], 'loaded', $user);
    }

    public function dispatch(Shipments $shipment, $user = null): Shipments
    {
        return $this->transitionShipment($shipment, [
            'shipment_status' => 'in_transit',
            'departed_at' => now(),
        ], 'dispatched', $user);
    }

    public function completeStop(ShipmentRoute $route, ?string $receiverName = null, $user = null): ShipmentRoute
    {
        return DB::transaction(function () use ($route, $receiverName, $user) {
            $route = ShipmentRoute::whereKey($route->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $route->delivery_status ?: 'planned';

            $route->update([
                'status' => 1,
                'delivery_status' => 'delivered',
                'delivered_packs' => $route->planned_packs,
                'arrived_at' => $route->arrived_at ?: now(),
                'delivered_at' => now(),
                'receiver_name' => $receiverName,
            ]);

            $shipment = $route->shipment;
            if ($shipment && $this->allStopsFinished($shipment)) {
                $shipment->update(['status' => 1, 'shipment_status' => 'completed', 'loading_status' => 'closed', 'completed_at' => now()]);
            }

            $this->recordEvent($shipment, $route, 'stop_delivered', $fromStatus, 'delivered', null, $user);

            return $route->fresh(['shipment', 'pishfactor']) ?: $route;
        });
    }

    public function failStop(ShipmentRoute $route, string $reason, $user = null): ShipmentRoute
    {
        return DB::transaction(function () use ($route, $reason, $user) {
            $route = ShipmentRoute::whereKey($route->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $route->delivery_status ?: 'planned';

            $route->update([
                'delivery_status' => 'failed',
                'failed_reason' => $reason,
                'arrived_at' => $route->arrived_at ?: now(),
            ]);

            $this->recordEvent($route->shipment, $route, 'stop_failed', $fromStatus, 'failed', $reason, $user);

            return $route->fresh(['shipment', 'pishfactor']) ?: $route;
        });
    }

    public function recalculateTotals(Shipments $shipment): Shipments
    {
        return DB::transaction(function () use ($shipment) {
            $shipment = Shipments::whereKey($shipment->id)->lockForUpdate()->firstOrFail();
            $routes = $shipment->routes()->where('stop_type', 'delivery')->get();
            $shipment->update([
                'total_orders' => $routes->whereNotNull('factor_id')->count(),
                'total_packs' => $routes->sum('planned_packs'),
            ]);

            return $shipment->fresh(['routes']) ?: $shipment;
        });
    }

    private function transitionShipment(Shipments $shipment, array $attributes, string $eventType, $user = null): Shipments
    {
        return DB::transaction(function () use ($shipment, $attributes, $eventType, $user) {
            $shipment = Shipments::whereKey($shipment->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $shipment->shipment_status ?: 'planned';

            if ($eventType === 'dispatched' && $shipment->loading_status !== 'loaded') {
                throw ValidationException::withMessages(['shipment' => 'Shipment must be loaded before dispatch.']);
            }

            $shipment->update($attributes);
            $this->recordEvent($shipment->fresh() ?: $shipment, null, $eventType, $fromStatus, $attributes['shipment_status'] ?? null, null, $user);

            return $shipment->fresh(['routes']) ?: $shipment;
        });
    }

    private function allStopsFinished(Shipments $shipment): bool
    {
        return !$shipment->routes()
            ->where('stop_type', 'delivery')
            ->whereNotIn('delivery_status', ['delivered', 'failed'])
            ->exists();
    }

    private function recordEvent(?Shipments $shipment, ?ShipmentRoute $route, string $eventType, ?string $fromStatus, ?string $toStatus, ?string $note, $user): void
    {
        if (!$shipment || !Schema::hasTable('shipment_events')) {
            return;
        }

        ShipmentEvent::create([
            'tenant_id' => $shipment->tenant_id,
            'organization_id' => (int) $shipment->organization_id ?: null,
            'shipment_id' => $shipment->id,
            'shipment_route_id' => $route?->id,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'description' => $note,
            'created_by' => $user?->id,
        ]);
    }
}
