<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;

class Tasks extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;
    protected $fillable = ['leader_id', 'user_id', 'area_id', 'date', 'start_date', 'end_date', 'senf', 'channel', 'status', 'min_sale_item', 'min_sale_price', 'min_sale_item_price', 'organization_id', 'tenant_id', 'updated_at'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}
