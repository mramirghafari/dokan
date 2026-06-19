/**
 * Shared DataTables defaults — branded loading overlay for server-side tables.
 */
(function ($) {
    'use strict';

    if (!$.fn.dataTable) {
        return;
    }

    var processingHtml = [
        '<div class="dokan-dt-processing" role="status" aria-live="polite" aria-busy="true">',
        '  <div class="dokan-dt-processing__spinner" aria-hidden="true"></div>',
        '  <p class="dokan-dt-processing__title">در حال بارگذاری...</p>',
        '  <p class="dokan-dt-processing__subtitle">لطفاً چند لحظه صبر کنید</p>',
        '</div>',
    ].join('');

    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            processing: processingHtml,
        },
    });
})(jQuery);
