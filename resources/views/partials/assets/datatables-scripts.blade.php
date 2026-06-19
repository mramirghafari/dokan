@php
    $bundle = $bundle ?? 'basic';
    $assets = asset('assets');
@endphp
<script src="{{ $assets }}/vendor/libs/datatables-bs5/js/jquery.dataTables.min.js"></script>
<script src="{{ $assets }}/vendor/libs/datatables-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ $assets }}/js/dokan-datatables.js"></script>
@if ($bundle === 'full')
    <script src="{{ $assets }}/vendor/libs/datatables-buttons-bs5/js/dataTables.buttons.min.js"></script>
    <script src="{{ $assets }}/vendor/libs/datatables-buttons-bs5/js/buttons.bootstrap5.min.js"></script>
    <script src="{{ $assets }}/vendor/libs/datatables-fixedheader-bs5/js/dataTables.fixedHeader.min.js"></script>
    <script src="{{ $assets }}/vendor/libs/jszip/jszip.min.js"></script>
    <script src="{{ $assets }}/vendor/libs/pdfmake/pdfmake.min.js"></script>
    <script src="{{ $assets }}/vendor/libs/pdfmake/vfs_fonts.js"></script>
    <script src="{{ $assets }}/vendor/libs/datatables-buttons-bs5/js/buttons.html5.min.js"></script>
    <script src="{{ $assets }}/vendor/libs/datatables-buttons-bs5/js/buttons.print.min.js"></script>
    <script src="{{ $assets }}/vendor/libs/datatables-buttons-bs5/js/buttons.colVis.min.js"></script>
@endif
