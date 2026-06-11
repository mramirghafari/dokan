<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmCollaborationComment extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'commentable_type',
        'commentable_id',
        'user_id',
        'body',
        'mentioned_user_ids',
        'visibility',
        'is_pinned',
        'source',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'mentioned_user_ids' => 'array',
        'is_pinned' => 'boolean',
    ];

    public const VISIBILITIES = [
        'team' => 'تیم داخلی',
        'manager' => 'مدیران',
        'private' => 'خصوصی',
    ];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mentions()
    {
        return $this->hasMany(CrmCollaborationMention::class, 'comment_id');
    }

    public function visibilityText(): string
    {
        return self::VISIBILITIES[$this->visibility] ?? $this->visibility;
    }
}
