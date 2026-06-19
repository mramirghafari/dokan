<div class="mb-3 col-12 col-md-6">
    <label class="form-label" for="business_profile">پروفایل کسب‌وکار / قالب خط فاکتور</label>
    <select class="select2 form-select" data-allow-clear="true" id="business_profile" name="business_profile">
        @foreach ($layoutProfiles as $profile)
            <option value="{{ $profile['key'] }}"
                @if((isset($factorMaker) ? ($factorMaker->business_profile ?? 'distribution') : 'distribution') === $profile['key']) selected @endif>
                {{ $profile['label'] }}
            </option>
        @endforeach
    </select>
    <small class="text-muted" id="business_profile_help">
        {{ $layoutConfig[isset($factorMaker) ? ($factorMaker->business_profile ?? 'distribution') : 'distribution']['description'] ?? '' }}
    </small>
</div>

<div class="col-12">
    <div class="alert alert-info py-2 mb-3">
        ستون‌های خط فاکتور بر اساس پروفایل انتخاب‌شده تغییر می‌کند. برای پخش و فروش کالا می‌توانید
        <strong>نام ستون واحد اصلی</strong> (عدد، بطری، قرص ...)،
        <strong>واحد فرعی</strong> (کارتن، جعبه، پک ...)،
        <strong>وزن</strong> (گرم، کیلو، تن، میلی‌گرم، سوت ...) و
        <strong>فی واحد</strong> را مطابق کسب‌وکار خود تنظیم کنید.
    </div>
</div>

<div class="row" id="layout-label-fields">
    @php
        $groupedFields = collect($labelFields)->groupBy('group');
        $groupOrder = ['unit', 'pricing', 'content', 'other'];
    @endphp
    @foreach ($groupOrder as $group)
        @if ($groupedFields->has($group))
            <div class="col-12 layout-label-group" data-group="{{ $group }}">
                <h6 class="mb-2 mt-1">{{ config('invoice_layouts.label_field_groups.' . $group, $group) }}</h6>
            </div>
            @foreach ($groupedFields[$group] as $field)
                <div class="mb-3 col-12 col-md-4 col-lg-3 layout-label-field" data-label-key="{{ $field['key'] }}">
                    <label class="form-label" for="{{ $field['input'] }}">{{ $field['label'] }}</label>
                    <input class="form-control layout-label-input"
                           id="{{ $field['input'] }}"
                           name="{{ $field['input'] }}"
                           type="text"
                           @if (!empty($field['presets'])) list="preset-list-{{ $field['key'] }}" @endif
                           placeholder="{{ $field['default'] }}"
                           value="{{ isset($factorMaker) ? ($factorMaker->line_layout['labels'][$field['key']] ?? '') : '' }}" />
                    @if (!empty($field['presets']))
                        <datalist id="preset-list-{{ $field['key'] }}">
                            @foreach ($field['presets'] as $preset)
                                <option value="{{ $preset }}"></option>
                            @endforeach
                        </datalist>
                    @endif
                    @if (!empty($field['hint']))
                        <small class="text-muted d-block mt-1">{{ $field['hint'] }}</small>
                    @endif
                </div>
            @endforeach
        @endif
    @endforeach
</div>
