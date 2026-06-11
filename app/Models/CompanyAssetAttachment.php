<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyAssetAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_asset_id',
        'tenant_id',
        'organization_id',
        'attachment_type',
        'disk',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'note',
        'created_by',
    ];

    public function asset()
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }
}
