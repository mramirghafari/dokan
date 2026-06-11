<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;
    protected $fillable = ['fromOrganization', 'toOrganization', 'tenant_id', 'product_id', 'stock_id', 'user_id', 'AmvalCode', 'number', 'transferDate', 'approveUser', 'denyUser', 'isRead', 'isApproved', 'description', 'isDenied', 'approvedNumber'];

    public function fromOrganization()
    {
        return $this->belongsTo(Organization::class, 'fromOrganization');
    }

    public function toOrganization()
    {
        return $this->belongsTo(Organization::class, 'toOrganization');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approveUser()
    {
        return $this->belongsTo(User::class, 'approveUser');
    }

    public function denyUser()
    {
        return $this->belongsTo(User::class, 'denyUser');
    }
}
