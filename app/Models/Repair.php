<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repair extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "repairs";
    protected $fillable = ['title', 'parentCategory_id', 'childCategory_id', 'store_id', 'entity', 'brand_id', 'isActive', 'user_id', 'description', 'organization_id', 'tenant_id', 'employee_id', 'inputDate', 'outputDate'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }


    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }


    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parentCategory_id');
    }

    public function childCategory()
    {
        return $this->belongsTo(Category::class, 'childCategory_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getCreatedAtAttribute($created_at)
    {
        $v1 = new \Hekmatinasser\Verta\Verta($created_at);
        $v1 = $v1->format('H:m:s - Y/m/d');
        return $v1;
    }

    public function getUpdatedAtAttribute($updated_at)
    {
        $v1 = new \Hekmatinasser\Verta\Verta($updated_at);
        $v1 = $v1->format('H:m:s - Y/m/d');
        return $v1;
    }
}
