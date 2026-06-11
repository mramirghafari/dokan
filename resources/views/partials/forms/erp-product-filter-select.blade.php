@php
    $productFilters = config('erp_scale.remote_lookup.product_filters', ['is_active' => 1, 'is_material' => 0]);
@endphp
<x-erp-remote-select
    entity="products"
    :name="$name ?? 'product_id'"
    :value="$value ?? request('product_id')"
    :placeholder="$placeholder ?? 'همه کالاها'"
    :class="$class ?? 'select2 form-select erp-remote-select'"
    :filters="$productFilters"
/>
