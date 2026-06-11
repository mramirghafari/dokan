<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpScaleAuditSnapshot extends Model
{
    use HasFactory, HasOrganizationFilter;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'scope_label',
        'readiness_score',
        'risk_level',
        'summary',
        'checks',
        'table_profiles',
        'recommendations',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'summary' => 'array',
        'checks' => 'array',
        'table_profiles' => 'array',
        'recommendations' => 'array',
        'generated_at' => 'datetime',
        'readiness_score' => 'integer',
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
