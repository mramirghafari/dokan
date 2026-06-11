<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\DistributionVisitPlan;
use App\Models\DistributionVisitStop;
use App\Models\Pishfactor;
use App\Models\Product;
use App\Models\ShipmentRoute;
use App\Models\Shipments;
use App\Models\User;
use App\Services\DistributionSalesService;
use App\Services\DistributionWorkflowService;
use App\Services\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required_without:mobile', 'string'],
            'mobile' => ['required_without:username', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::query()
            ->where(function ($query) use ($data) {
                if (!empty($data['username'])) {
                    $query->where('username', $data['username'])->orWhere('email', $data['username']);
                }

                if (!empty($data['mobile'])) {
                    $query->orWhere('mobile', $data['mobile']);
                }
            })
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['username' => 'Invalid login credentials.']);
        }

        if ($message = $user->loginBlockMessage()) {
            throw ValidationException::withMessages(['username' => $message]);
        }

        $token = $user->createToken($data['device_name'] ?? 'mobile-app')->plainTextToken;

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['status' => 'ok']);
    }

    public function me(Request $request, TenantContextService $context): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
            'context' => $context->fromUser($request->user()),
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = Pishfactor::forOrganizations($user);
        $driverShipments = Shipments::query()
            ->where('driver_id', $user->id)
            ->whereIn('shipment_status', ['planned', 'loaded', 'in_transit']);

        return response()->json([
            'orders' => [
                'draft' => (clone $orders)->where('sales_status', 'draft')->count(),
                'pending_approval' => (clone $orders)->where('approval_status', 'pending_approval')->count(),
                'approved' => (clone $orders)->where('approval_status', 'approved')->count(),
                'ready_for_delivery' => (clone $orders)->where('delivery_status', 'ready')->count(),
                'delivered' => (clone $orders)->where('delivery_status', 'delivered')->count(),
            ],
            'driver' => [
                'active_shipments' => $driverShipments->count(),
            ],
        ]);
    }

    public function customers(Request $request): JsonResponse
    {
        $customers = Customers::forOrganizations($request->user())
            ->select(['id', 'name', 'mobile', 'phone', 'tablo', 'customer_code', 'status', 'area', 'region_id', 'address', 'shop_lat', 'shop_lng', 'customer_group_id', 'sales_channel_id', 'customer_status_id'])
            ->with(['customerGroup:id,title,type', 'salesChannel:id,title,type', 'customerStatus:id,title,type'])
            ->latest('id')
            ->limit((int) $request->integer('limit', 100))
            ->get();

        return response()->json(['data' => $customers]);
    }

    public function products(Request $request): JsonResponse
    {
        $products = Product::forOrganizations($request->user())
            ->select(['id', 'title', 'display_name', 'sku', 'price', 'discount', 'tax', 'pack_items', 'base_unit_id', 'secondary_unit_id', 'product_type', 'stock_tracking_mode', 'isActive'])
            ->with(['baseUnit:id,title,symbol', 'secondaryUnit:id,title,symbol'])
            ->where('isActive', 1)
            ->latest('id')
            ->limit((int) $request->integer('limit', 100))
            ->get();

        return response()->json(['data' => $products]);
    }

    public function orders(Request $request): JsonResponse
    {
        $orders = Pishfactor::forOrganizations($request->user())
            ->with(['customer:id,name,mobile,tablo,address,shop_lat,shop_lng', 'items.product:id,title,display_name,pack_items', 'mobileOrder'])
            ->when($request->filled('status'), fn($query) => $query->where('sales_status', $request->string('status')))
            ->when($request->filled('sync_status'), fn($query) => $query->where('sync_status', $request->string('sync_status')))
            ->latest('id')
            ->limit((int) $request->integer('limit', 100))
            ->get();

        return response()->json(['data' => $orders]);
    }

    public function visitPlans(Request $request): JsonResponse
    {
        $plans = DistributionVisitPlan::query()
            ->with(['stops.customer:id,name,mobile,tablo,address,shop_lat,shop_lng,area,region_id', 'stops.pishfactor:id,invoiceID,fullPrice,sales_status,sync_status'])
            ->where('visitor_id', $request->user()->id)
            ->when($request->filled('date'), fn($query) => $query->whereDate('planned_date_en', $request->date('date')))
            ->latest('planned_date_en')
            ->latest('id')
            ->limit((int) $request->integer('limit', 20))
            ->get();

        return response()->json(['data' => $plans]);
    }

    public function promotions(Request $request, DistributionSalesService $distributionSales): JsonResponse
    {
        return response()->json(['data' => $distributionSales->activePromotions($request->user())->values()]);
    }

    public function checkInVisitStop(Request $request, DistributionVisitStop $visitStop, DistributionSalesService $distributionSales): JsonResponse
    {
        $payload = $request->validate([
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'client_uid' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        return response()->json(['data' => $distributionSales->checkInStop($visitStop, $payload, $request->user())]);
    }

    public function noOrderVisitStop(Request $request, DistributionVisitStop $visitStop, DistributionSalesService $distributionSales): JsonResponse
    {
        $payload = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'client_uid' => ['nullable', 'string', 'max:120'],
        ]);

        return response()->json(['data' => $distributionSales->markNoOrder($visitStop, $payload, $request->user())]);
    }

    public function storeMobileOrder(Request $request, DistributionSalesService $distributionSales): JsonResponse
    {
        $payload = $request->validate([
            'client_order_uid' => ['nullable', 'string', 'max:120'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'visit_stop_id' => ['nullable', 'integer', 'exists:distribution_visit_stops,id'],
            'order_type' => ['nullable', 'in:hot_sale,cold_sale,preorder,return'],
            'sale_mode' => ['nullable', 'in:field,phone,online'],
            'payment_method' => ['nullable', 'integer', 'in:1,2,3,4'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'offline_created_at' => ['nullable', 'date'],
            'sync_status' => ['nullable', 'in:synced,offline,pending,conflict'],
            'collection_amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.unit_id' => ['nullable', 'integer'],
            'items.*.pack' => ['nullable', 'numeric', 'min:0'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        return response()->json(['data' => $distributionSales->createMobileOrder($payload, $request->user())], 201);
    }

    public function syncPush(Request $request, DistributionSalesService $distributionSales): JsonResponse
    {
        $payload = $request->validate([
            'items' => ['required', 'array'],
            'items.*.client_uid' => ['required', 'string', 'max:120'],
            'items.*.entity_type' => ['required', 'string', 'max:60'],
            'items.*.action' => ['nullable', 'string', 'max:40'],
            'items.*.payload' => ['nullable', 'array'],
        ]);

        return response()->json(['data' => $distributionSales->syncPush($payload, $request->user())]);
    }

    public function driverShipments(Request $request): JsonResponse
    {
        $shipments = Shipments::query()
            ->where('driver_id', $request->user()->id)
            ->whereIn('shipment_status', ['planned', 'loaded', 'in_transit'])
            ->with(['routes.pishfactor.customer:id,name,mobile,tablo,address,shop_lat,shop_lng'])
            ->latest('id')
            ->get();

        return response()->json(['data' => $shipments]);
    }

    public function deliverStop(Request $request, ShipmentRoute $shipmentRoute, DistributionWorkflowService $distribution): JsonResponse
    {
        $this->ensureDriverOwnsRoute($request, $shipmentRoute);

        $data = $request->validate([
            'receiver_name' => ['nullable', 'string', 'max:255'],
        ]);

        $route = $distribution->completeStop($shipmentRoute, $data['receiver_name'] ?? null, $request->user());

        return response()->json(['data' => $route]);
    }

    public function failStop(Request $request, ShipmentRoute $shipmentRoute, DistributionWorkflowService $distribution): JsonResponse
    {
        $this->ensureDriverOwnsRoute($request, $shipmentRoute);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $route = $distribution->failStop($shipmentRoute, $data['reason'], $request->user());

        return response()->json(['data' => $route]);
    }

    private function ensureDriverOwnsRoute(Request $request, ShipmentRoute $route): void
    {
        $route->loadMissing('shipment');

        if (!$route->shipment || (int) $route->shipment->driver_id !== (int) $request->user()->id) {
            abort(403, 'This shipment is not assigned to the current driver.');
        }
    }

    private function userPayload(User $user): array
    {
        $user->loadMissing('roles:id,title');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'mobile' => $user->mobile,
            'tenant_id' => $user->tenant_id ?: $user->tenants_id,
            'organization_id' => $user->organization_id,
            'roles' => $user->roles->pluck('title')->values(),
        ];
    }
}
