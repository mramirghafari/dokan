    const layoutProfiles = @json($layoutConfig);
    const layoutColumnLabels = @json(config('invoice_layouts.column_labels'));
    const layoutLabelFieldMeta = @json(config('invoice_layouts.label_field_meta'));
    const layoutLabelFieldGroups = @json(config('invoice_layouts.label_field_groups'));
    const savedLayoutLabels = @json($savedLayoutLabels ?? []);

    function escapeLayoutHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/"/g, '&quot;');
    }

    function selectedToggleValue(name) {
        const value = $(`[name="${name}"]`).val();
        return value === '1' ? 1 : 2;
    }

    function layoutLabelFieldHtml(key, defaults) {
        const meta = layoutLabelFieldMeta[key] || {};
        const title = meta.title || layoutColumnLabels[key] || key;
        const hint = meta.hint || '';
        const presets = meta.presets || [];
        const currentValue = $(`#label_${key}`).val() || savedLayoutLabels[key] || '';
        const listId = presets.length ? `preset-list-${key}` : '';
        const presetOptions = presets.map((preset) => `<option value="${escapeLayoutHtml(preset)}">`).join('');

        return `
            <div class="mb-3 col-12 col-md-4 col-lg-3 layout-label-field" data-label-key="${key}">
                <label class="form-label" for="label_${key}">${escapeLayoutHtml(title)}</label>
                <input class="form-control layout-label-input"
                       id="label_${key}"
                       name="label_${key}"
                       type="text"
                       ${listId ? `list="${listId}"` : ''}
                       placeholder="${escapeLayoutHtml(defaults[key] || '')}"
                       value="${escapeLayoutHtml(currentValue)}" />
                ${presets.length ? `<datalist id="${listId}">${presetOptions}</datalist>` : ''}
                ${hint ? `<small class="text-muted d-block mt-1">${escapeLayoutHtml(hint)}</small>` : ''}
            </div>`;
    }

    function renderLayoutLabelFields(profileKey) {
        const profile = layoutProfiles[profileKey] || layoutProfiles.distribution;
        const defaults = profile.default_labels || {};
        const keys = Object.keys(defaults);
        const groupedKeys = {};

        keys.forEach((key) => {
            const group = (layoutLabelFieldMeta[key] || {}).group || 'other';
            groupedKeys[group] = groupedKeys[group] || [];
            groupedKeys[group].push(key);
        });

        const groupOrder = ['unit', 'pricing', 'content', 'other'];
        const html = groupOrder
            .filter((group) => (groupedKeys[group] || []).length)
            .map((group) => {
                const groupLabel = layoutLabelFieldGroups[group] || group;
                const fields = groupedKeys[group].map((key) => layoutLabelFieldHtml(key, defaults)).join('');

                return `
                    <div class="col-12 layout-label-group" data-group="${group}">
                        <h6 class="mb-2 mt-1">${escapeLayoutHtml(groupLabel)}</h6>
                    </div>
                    ${fields}`;
            })
            .join('');

        $('#layout-label-fields').html(html);
        $('#business_profile_help').text(profile.description || '');
    }

    function renderLayoutPreview() {
        const profileKey = $('#business_profile').val() || 'distribution';
        const profile = layoutProfiles[profileKey] || layoutProfiles.distribution;
        const toggles = {
            column_pr_code: selectedToggleValue('column_pr_code'),
            column_moadian: selectedToggleValue('column_moadian'),
            column_sub_unit: selectedToggleValue('column_sub_unit'),
            column_discount: selectedToggleValue('column_discount'),
            column_tax: selectedToggleValue('column_tax'),
        };

        const columns = (profile.columns || []).filter((column) => {
            if (column.always) {
                return true;
            }
            if (column.toggle) {
                return toggles[column.toggle] === 1;
            }

            return true;
        }).map((column) => {
            const custom = $(`#label_${column.key}`).val();
            const label = custom
                || (profile.default_labels && profile.default_labels[column.key])
                || layoutColumnLabels[column.key]
                || column.key;

            return { ...column, label };
        });

        const header = columns.map((column) => `<th class="text-center">${escapeLayoutHtml(column.label)}</th>`).join('');
        const body = columns.map(() => '<td class="text-center">---</td>').join('');
        $('#layout-preview-header').html(header);
        $('#layout-preview-body tr').html(body);
    }

    const productTypeProfiles = @json($productTypeProfileMap ?? []);
    const productTypeDescriptions = @json(collect(config('factor_product_types.types', []))->map(fn ($t) => $t['description'] ?? '')->all());

    $('#business_profile').on('change', function () {
        renderLayoutLabelFields($(this).val() || 'distribution');
        renderLayoutPreview();
    });

    // انتخاب «نوع محصولات» به‌صورت خودکار پروفایل ستون‌بندی فاکتور را تنظیم می‌کند.
    $('#pr_type').on('change', function () {
        const typeKey = $(this).val();
        $('#pr_type_help').text(productTypeDescriptions[typeKey] || '');

        const profile = productTypeProfiles[typeKey];
        if (profile && $('#business_profile').val() !== profile) {
            $('#business_profile').val(profile).trigger('change');
        }
    });

    $(document).on('input', '.layout-label-input', renderLayoutPreview);
    $(document).on('change', '[name="column_pr_code"], [name="column_moadian"], [name="column_sub_unit"], [name="column_discount"], [name="column_tax"]', renderLayoutPreview);

    renderLayoutLabelFields($('#business_profile').val() || 'distribution');
    renderLayoutPreview();
