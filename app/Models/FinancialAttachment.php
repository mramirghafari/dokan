<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class FinancialAttachment extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'attachable_type',
        'attachable_id',
        'voucher_id',
        'attachment_kind',
        'disk',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'note',
        'created_by',
        'updated_by',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function getUrlAttribute(): string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk ?: 'public');

        return $disk->url($this->file_path);
    }
}
