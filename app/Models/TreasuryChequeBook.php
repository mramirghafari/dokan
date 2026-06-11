<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreasuryChequeBook extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'account_id',
        'book_number',
        'cheque_prefix',
        'first_leaf_number',
        'last_leaf_number',
        'next_leaf_number',
        'leaf_count',
        'warning_threshold',
        'bank_name',
        'branch_name',
        'account_number',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    public function account()
    {
        return $this->belongsTo(Accounts::class, 'account_id');
    }

    public function leaves()
    {
        return $this->hasMany(TreasuryChequeLeaf::class)->orderBy('leaf_number');
    }

    public function availableLeaves()
    {
        return $this->hasMany(TreasuryChequeLeaf::class)->where('status', 'available')->orderBy('leaf_number');
    }
}
