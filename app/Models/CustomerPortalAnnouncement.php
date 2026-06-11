<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPortalAnnouncement extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'audience_type',
        'priority',
        'title',
        'body',
        'starts_at',
        'ends_at',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public const AUDIENCES = ['all' => 'همه', 'customer' => 'مشتریان', 'representative' => 'نمایندگان'];
    public const PRIORITIES = ['normal' => 'عادی', 'important' => 'مهم', 'urgent' => 'فوری'];

    public function audienceText(): string
    {
        return self::AUDIENCES[$this->audience_type] ?? $this->audience_type;
    }

    public function priorityText(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }
}
