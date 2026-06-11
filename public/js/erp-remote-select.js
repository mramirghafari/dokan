(function (window, $) {
    'use strict';

    if (!$) {
        return;
    }

    function parseFilters($element) {
        const raw = $element.attr('data-filters') || '{}';

        try {
            return JSON.parse(raw);
        } catch (error) {
            return {};
        }
    }

    function initElement($element) {
        if (!$element.length || !$element.hasClass('erp-remote-select')) {
            return;
        }

        if ($element.data('erpRemoteSelect')) {
            return;
        }

        const lookupUrl = $element.data('lookup-url');
        const entity = $element.data('entity');
        if (!lookupUrl || !entity) {
            return;
        }

        const allowClear = String($element.data('allow-clear')) !== '0';
        const minimumInputLength = parseInt($element.data('minimum-input-length'), 10) || 2;
        const placeholder = $element.data('placeholder') || 'انتخاب کنید';
        const filters = parseFilters($element);
        const isMultiple = !!$element.prop('multiple');
        const $modalParent = $element.closest('.modal');
        const select2Config = {
            width: '100%',
            dir: 'rtl',
            allowClear: allowClear,
            placeholder: placeholder,
            minimumInputLength: minimumInputLength,
            multiple: isMultiple,
            language: {
                inputTooShort: function () {
                    return 'حداقل ' + minimumInputLength + ' کاراکتر وارد کنید';
                },
                searching: function () {
                    return 'در حال جستجو...';
                },
                noResults: function () {
                    return 'موردی یافت نشد';
                },
            },
            ajax: {
                url: lookupUrl,
                dataType: 'json',
                delay: 300,
                data: function (params) {
                    return {
                        entity: entity,
                        q: params.term || '',
                        limit: 20,
                        filters: filters,
                    };
                },
                processResults: function (data) {
                    return {
                        results: (data.results || []).map(function (row) {
                            return {
                                id: row.id,
                                text: row.text,
                            };
                        }),
                    };
                },
                cache: true,
            },
        };

        if ($modalParent.length) {
            select2Config.dropdownParent = $modalParent;
        }

        $element.select2(select2Config);

        $element.data('erpRemoteSelect', true);
    }

    function destroyElement($element) {
        if ($element.data('select2')) {
            $element.select2('destroy');
        }

        $element.removeData('erpRemoteSelect');
    }

    window.ErpRemoteSelect = {
        init: function (root) {
            const $root = root ? $(root) : $(document);
            $root.find('select.erp-remote-select').each(function () {
                initElement($(this));
            });
        },
        destroy: function (root) {
            const $root = root ? $(root) : $(document);
            $root.find('select.erp-remote-select').each(function () {
                destroyElement($(this));
            });
        },
        reinit: function (root) {
            this.destroy(root);
            this.init(root);
        },
    };

    $(function () {
        window.ErpRemoteSelect.init(document);
    });
})(window, window.jQuery);
