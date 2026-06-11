<tr>
    <td style="min-width: 260px">
        <x-erp-remote-select
            entity="products"
            :name="'items[' . $index . '][product_id]'"
            placeholder="انتخاب کالا"
            class="form-select erp-remote-select"
            :filters="config('erp_scale.remote_lookup.product_filters')"
        />
    </td>
    <td style="min-width: 220px">
        <select class="form-select" name="items[{{ $index }}][warehouse_location_id]">
            <option value="">انتخاب مکان</option>
            @foreach ($warehouseLocations as $location)
                <option value="{{ $location->id }}">{{ $location->store->title ?? '' }} / {{ $location->title }}</option>
            @endforeach
        </select>
    </td>
    <td><input type="number" step="0.001" min="0" class="form-control" name="items[{{ $index }}][counted_quantity]" required></td>
    <td><input type="number" step="0.01" min="0" class="form-control" name="items[{{ $index }}][unit_cost]"></td>
    <td><input type="text" class="form-control" name="items[{{ $index }}][description]"></td>
    <td><button type="button" class="btn btn-sm btn-label-danger remove-adjustment-row">حذف</button></td>
</tr>
