<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;
    protected $fillable = ['employee_id', 'product_id', 'user_id', 'number', 'description', 'deliverDate', 'AmvalCode', 'stock_id', 'organization_id', 'tenant_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function history()
    {
        return $this->belongsTo(History::class);
    }
}
