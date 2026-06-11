<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;
    protected $fillable = ['ip', 'delivery_id', 'user_id', 'tenant_id', 'action', 'store', 'description'];

    public function user()
    {
        return $this->BelongsTo(User::class);
    }

    public function setDescriptionAttribute($description)
    {
        $this->attributes['description'] = mb_strtolower($description);
    }

    public function getCreatedAtAttribute($created_at)
    {
        $v1 = new \Hekmatinasser\Verta\Verta($created_at);
        $v1 = $v1->format('H:i:s - Y/m/d');
        return $v1;
    }
}
