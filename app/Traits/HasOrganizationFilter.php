<?php

namespace App\Traits;

trait HasOrganizationFilter
{
    /**
     * برمی‌گردونه آرایه‌ای از organization_id های کاربر
     * هم به صورت int و هم string برای جستجو
     */
    public function getOrganizationIdsForSearchAttribute()
    {
        $ids = is_array(json_decode($this->organization_id))
            ? json_decode($this->organization_id)
            : [intval($this->organization_id)];

        $searchValues = [];

        foreach ($ids as $id) {
            $searchValues[] = intval($id);
            $searchValues[] = strval($id);
        }

        return $searchValues;
    }

    /**
     * کوئری اسکوپ برای فیلتر کردن بر اساس organization_id
     * هم برای JSON معتبر و هم برای int ساده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User $user
     * @param string $field نام ستون فیلتر (پیشفرض organization_id)
     */
    public function scopeForOrganizations($query, $user, $field = 'organization_id')
    {
        if (!$user || $user->isGod == 1) {
            return $query;
        }

        // اگر از Product مدل اومده و فیلد بدون نقطه است، خودمون prefix می‌کنیم
        if (strpos($field, '.') === false) {
            $model = $query->getModel();
            $table = $model->getTable();
            $field = $table . '.' . $field;
        }

        $query->where(function ($q) use ($user, $field) {
            foreach ($user->organization_ids_for_search as $orgId) {
                $q->orWhere(function ($sub) use ($field, $orgId) {
                    $sub->whereRaw("JSON_VALID({$field})")
                        ->whereJsonContains($field, $orgId);
                })
                    ->orWhere($field, $orgId);
            }
        });

        return app(\App\Services\PermissionScopeService::class)
            ->applyOperationalScopes($query, $user, ['organization' => $field]);
    }
}
