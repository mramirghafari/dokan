<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmCollaborationMention extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'comment_id',
        'mentioned_user_id',
        'mentioned_by_user_id',
        'mentionable_type',
        'mentionable_id',
        'notified_at',
        'read_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function comment()
    {
        return $this->belongsTo(CrmCollaborationComment::class, 'comment_id');
    }

    public function mentionedUser()
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }

    public function mentionedBy()
    {
        return $this->belongsTo(User::class, 'mentioned_by_user_id');
    }

    public function mentionable()
    {
        return $this->morphTo();
    }
}
