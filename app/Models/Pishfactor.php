<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;
use App\Traits\SyncsTenantColumns;

class Pishfactor extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes, HasOrganizationFilter, SyncsTenantColumns;
    protected $fillable = ['customer_id', 'visitor_id', 'sarparast_id', 'updated_by', 'driver_id', 'payment_type', 'check_status', 'recive_date', 'recive_date_en', 'invoiceID', 'mobile_order_uid', 'distribution_order_type', 'sale_mode', 'visit_stop_id', 'promotion_discount_amount', 'offline_created_at', 'sync_status', 'sales_document_type', 'sales_status', 'approval_status', 'approval_level', 'approval_requested_at', 'approval_requested_by', 'approval_reviewed_at', 'approval_reviewed_by', 'approval_note', 'credit_status', 'credit_limit_snapshot', 'customer_balance_snapshot', 'reserve_status', 'reserved_at', 'warehouse_issue_status', 'settlement_status', 'delivery_status', 'delivered_at', 'price_list_id', 'revenue_center_id', 'project_code', 'contract_code', 'route_code', 'status', 'step', 'pat_price', 'fullPrice', 'tozihat', 'organization_id', 'tenants_id', 'tenant_id', 'task_id', 'crm_sales_board_card_id', 'area_id', 'region_id', 'city_id', 'shipment_id', 'create_lat', 'create_lng', 'created_at', 'is_agency_order', 'agency_user_id'];

    protected $casts = [
        'is_agency_order' => 'boolean',
        'approval_requested_at' => 'datetime',
        'approval_reviewed_at' => 'datetime',
        'reserved_at' => 'datetime',
        'delivered_at' => 'datetime',
        'promotion_discount_amount' => 'decimal:2',
        'offline_created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id', 'id');
    }
    public function visitor()
    {
        return $this->belongsTo(User::class, 'visitor_id');
    }
    public function agencyUser()
    {
        return $this->belongsTo(User::class, 'agency_user_id');
    }
    public function leader()
    {
        return $this->belongsTo(User::class, 'sarparast_id');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function items()
    {
        return $this->hasMany(PishFactorItems::class, 'pishfactor_id');
    }

    public function workflowEvents()
    {
        return $this->hasMany(SalesWorkflowEvent::class, 'pishfactor_id');
    }

    public function reservations()
    {
        return $this->hasMany(SalesInventoryReservation::class, 'pishfactor_id');
    }

    public function mobileOrder()
    {
        return $this->hasOne(DistributionMobileOrder::class, 'pishfactor_id');
    }

    public function visitStop()
    {
        return $this->belongsTo(DistributionVisitStop::class, 'visit_stop_id');
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }
    public function products()
    {
        return $this->hasManyThrough(
            Product::class,          // مدل مقصد
            PishFactorItems::class,   // مدل واسط
            'pishfactor_id',         // کلید خارجی در جدول PishFactorItems
            'id',                    // کلید خارجی در جدول Products
            'id',                    // کلید محلی در جدول Pishfactor
            'pr_id'                  // کلید محلی در جدول PishFactorItems
        );
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function stores()
    {
        // گرفتن همه محصولات پیش‌فاکتور
        $products = $this->items()->with('product')->get()->pluck('product');

        // استخراج store_id ها (چون json هست باید decode کنیم)
        $storeIds = collect();
        foreach ($products as $product) {
            if (!empty($product->store_id)) {
                $ids = is_array($product->store_id)
                    ? $product->store_id
                    : json_decode($product->store_id, true);

                if (is_array($ids)) {
                    $storeIds = $storeIds->merge($ids);
                }
            }
        }

        // گرفتن انبارهای یکتا
        return \App\Models\Store::whereIn('id', $storeIds->unique())->get();
    }

    public function storeNames()
    {
        return $this->stores()->pluck('title')->toArray();
    }

    public function scopeNewCustomers($query, $firstDate = null)
    {
        $firstDate = $firstDate ? \Carbon\Carbon::parse($firstDate) : null;
        $today = \Carbon\Carbon::now();

        // شرط زمانی (در صورت ارسال تاریخ شروع)
        if ($firstDate) {
            $query->whereBetween('created_at', [$firstDate, $today]);
        }

        // شرطی که فقط فاکتورهای مشتریان جدید رو نگه‌داره
        $query->whereHas('customer', function ($q) use ($firstDate, $today) {
            if ($firstDate) {
                $q->whereBetween('created_at', [$firstDate, $today]);
            }
        });

        return $query;
    }
}
