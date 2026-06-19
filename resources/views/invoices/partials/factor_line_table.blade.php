<?php
    $layoutService = app(\App\Services\InvoiceLayoutService::class);
    $layout = $layoutService->resolveLayout($factorMaker);
    $footerTotals = [];
    foreach ($layout['columns'] as $column) {
        if (!empty($column['sum'])) {
            $footerTotals[$column['key']] = 0;
        }
    }
    $factorPrice = 0;
?>

<tr class="x_border td-left-border">
    @foreach ($layout['columns'] as $column)
        <th class="text-center @if($column['key'] === 'title' || in_array($column['key'], ['course', 'plan'], true)) kalaname @endif"
            @if(in_array($column['key'], ['title', 'course', 'plan'], true)) width="250" @endif>
            {{ $column['label'] }}
        </th>
    @endforeach
</tr>
</thead>

<tbody>
@php($rowNumber = 1)
@foreach ($storeGroup['products'] as $item)
    <?php
        $product = $item->product ?? \App\Models\Product::find($item->pr_id);
        $line = $layoutService->buildLineValues($item, $product, $layout);
        $factorPrice += (int) $line['net'];
        foreach ($footerTotals as $key => $value) {
            $footerTotals[$key] += (float) ($line[$key] ?? 0);
        }
    ?>
    <tr class="item_{{ $rowNumber }} x_border td-left-border" data-item="{{ $rowNumber }}">
        @foreach ($layout['columns'] as $column)
            <?php
                $columnKey = $column['key'];
                $cellValue = $columnKey === 'row' ? $rowNumber : ($line[$columnKey] ?? '---');
            ?>
            <td class="text-center @if($columnKey === 'title' || in_array($columnKey, ['course', 'plan', 'section'], true)) text-start @endif">
                {{ $columnKey === 'row' ? $cellValue : $layoutService->formatValue($columnKey, $cellValue) }}
            </td>
        @endforeach
    </tr>
    @php($rowNumber++)
@endforeach
</tbody>
<tfoot>
<tr>
    @php($footerStarted = false)
    @foreach ($layout['columns'] as $column)
        @php($columnKey = $column['key'])
        @if ($columnKey === 'row')
            <th>جمع کل</th>
        @elseif (!empty($column['sum']))
            <th class="text-center">{{ $layoutService->formatValue($columnKey, $footerTotals[$columnKey] ?? 0) }}</th>
        @elseif (!$footerStarted && !in_array($columnKey, ['discount_percent'], true))
            @php($footerStarted = true)
            <th class="text-center"></th>
        @else
            <th class="text-center"></th>
        @endif
    @endforeach
</tr>
<tr>
    <th colspan="{{ min(5, count($layout['columns'])) }}">شرایط و نحوه فروش:
        <span>
            <label class="@if ($PishFactor->payment_type == 1) active @endif">نقدی</label>
            <label class="@if ($PishFactor->payment_type == 2) active @endif">چکی</label>
        </span>
    </th>
    <th class="horof" colspan="{{ max(1, count($layout['columns']) - min(5, count($layout['columns']))) }}"></th>
</tr>
<tr>
    <th colspan="{{ count($layout['columns']) }}">توضیحات: {{ $PishFactor->tozihat }}</th>
</tr>
<tr>
    <th colspan="{{ count($layout['columns']) }}">این اقلام توسط فروشگاه {{ $PishFactor->customer->tablo }} تحویل گرفته شد و تا زمان وصول کامل مبلغ فاکتور، نزد مشتری گرامی به صورت امانی میباشد.</th>
</tr>
<tr>
    <th colspan="{{ (int) ceil(count($layout['columns']) / 3) }}" style="text-align: right !important; height: 100px !important;">مهر و امضای فروشنده</th>
    <th colspan="{{ (int) ceil(count($layout['columns']) / 3) }}" style="text-align: right !important; height: 100px !important;">مهر و امضای مسئول پخش</th>
    <th colspan="{{ max(1, count($layout['columns']) - (int) ceil(count($layout['columns']) / 3) * 2) }}" style="text-align: right !important; height: 100px !important;">مهر و امضای خریدار</th>
</tr>
<input type="hidden" name="fullPrice" value="{{ $factorPrice }}" />
</tfoot>
