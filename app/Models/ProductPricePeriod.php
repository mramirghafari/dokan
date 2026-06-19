<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPricePeriod extends Model
{
    use HasFactory;
    use BelongsToTenant;

    public const TYPE_SALE = "sale";
    public const TYPE_BUY = "buy";
    public const TYPE_COST = "cost";
    public const TYPE_AGENT = "agent";
    public const TYPE_WHOLESALE = "wholesale";
    public const TYPE_CONSUMER = "consumer";
    public const TYPE_PREPAYMENT = "prepayment";
    public const TYPE_COMPLETION = "completion";

    public const PRICE_TYPES = [
        self::TYPE_SALE => "قیمت فروش",
        self::TYPE_BUY => "قیمت خرید",
        self::TYPE_COST => "قیمت تمام شده",
        self::TYPE_AGENT => "قیمت نماینده",
        self::TYPE_WHOLESALE => "قیمت عمده",
        self::TYPE_CONSUMER => "قیمت مصرف کننده",
        self::TYPE_PREPAYMENT => "پیش‌پرداخت",
        self::TYPE_COMPLETION => "تکمیل وجه",
    ];

    protected $fillable = [
        "tenant_id",
        "product_id",
        "price_type",
        "amount",
        "starts_at",
        "ends_at",
        "starts_at_fa",
        "ends_at_fa",
        "priority",
        "status",
        "metadata",
    ];

    protected $casts = [
        "starts_at" => "date",
        "ends_at" => "date",
        "amount" => "decimal:2",
        "metadata" => "array",
        "priority" => "integer",
        "status" => "boolean",
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

