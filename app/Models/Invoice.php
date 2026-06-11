<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = ['invoiceID', 'shopName', 'phone', 'address', 'buyDate', 'inputDate', 'user_id', 'tenant_id', 'description', 'price', 'file'];

    public function details()
    {
        return $this->belongsToMany(Detail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
