<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;


class Transportation extends Model
{
    use HasFactory,SoftDeletes,HasOrganizationFilter;
    public $timestamps = false; // created_at و updated_at رو غیرفعال می‌کنه
    protected $fillable = ['driver_id','cartons','weight'];

}
