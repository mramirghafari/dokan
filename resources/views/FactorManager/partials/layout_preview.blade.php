@php
    $previewLayout = app(\App\Services\InvoiceLayoutService::class)->resolveLayout($factorMaker);
@endphp

<tr class="x_border td-left-border" id="layout-preview-header">
    @foreach ($previewLayout['columns'] as $column)
        <th class="text-center @if(in_array($column['key'], ['title', 'course', 'plan'], true)) kalaname @endif">
            {{ $column['label'] }}
        </th>
    @endforeach
</tr>
</thead>
<tbody id="layout-preview-body">
<tr class="x_border td-left-border">
    @foreach ($previewLayout['columns'] as $column)
        <td class="text-center @if(in_array($column['key'], ['title', 'course', 'plan', 'section'], true)) text-start @endif">---</td>
    @endforeach
</tr>
</tbody>
