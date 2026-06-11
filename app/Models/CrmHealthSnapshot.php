<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmHealthSnapshot extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'scope_label',
        'health_score',
        'risk_level',
        'summary',
        'issues',
        'recommendations',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'summary' => 'array',
        'issues' => 'array',
        'recommendations' => 'array',
        'generated_at' => 'datetime',
        'health_score' => 'integer',
    ];

    public const RISK_LEVELS = [
        'low' => 'کم',
        'medium' => 'متوسط',
        'high' => 'بالا',
        'critical' => 'بحرانی',
    ];

    public function riskText(): string
    {
        return self::RISK_LEVELS[$this->risk_level] ?? $this->risk_level;
    }
}
