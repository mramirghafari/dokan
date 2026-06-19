{{--
    اسکریپت سلکتور آبشاری حساب. درختِ حساب‌ها را به JS می‌دهد و رفتار آبشاری را
    (با event delegation برای ردیف‌های پویا) مدیریت می‌کند.
    نیازمند jQuery + select2 که قبلاً در صفحه بارگذاری شده‌اند.
    ورودی: $accounts (مجموعهٔ تخت حساب‌ها)
--}}
@php
    $accountCascaderTree = ['byParent' => [], 'byId' => []];
    foreach ($accounts as $account) {
        $node = [
            'id' => (int) $account->id,
            'code' => (string) $account->code,
            'name' => (string) $account->name,
            'level' => (int) $account->level,
            'parent_id' => (int) ($account->parent_id ?? 0),
        ];
        $accountCascaderTree['byParent'][(string) $node['parent_id']][] = $node;
        $accountCascaderTree['byId'][(string) $node['id']] = $node;
    }
@endphp
<script>
    window.AccountCascader = (function () {
        var tree = @json($accountCascaderTree);
        var byParent = tree.byParent || {};
        var byId = tree.byId || {};

        function label(node) {
            return node.code + ' - ' + node.name;
        }

        function isSelect2($el) {
            return $el.hasClass('select2-hidden-accessible');
        }

        function refresh($el) {
            if (isSelect2($el)) {
                $el.trigger('change.select2');
            }
        }

        // پر کردن یک select با فرزندان parentId (در غیر این صورت فقط placeholder).
        function fill($select, parentId, placeholder) {
            $select.empty();
            $select.append(new Option(placeholder, '', false, false));
            var children = byParent[String(parentId)] || [];
            children.forEach(function (node) {
                $select.append(new Option(label(node), node.id, false, false));
            });
        }

        function syncHidden($wrap) {
            var tafsil = $wrap.find('.account-cascader-tafsil').val();
            var moein = $wrap.find('.account-cascader-moein').val();
            var kol = $wrap.find('.account-cascader-kol').val();
            $wrap.find('.account-cascader-id').val(tafsil || moein || kol || '');
        }

        // زنجیرهٔ نیاکان یک حساب از ریشه تا خودش.
        function ancestry(accountId) {
            var node = byId[String(accountId)];
            var chain = [];
            var guard = 0;
            while (node && guard < 20) {
                chain.unshift(node);
                if (!node.parent_id) {
                    break;
                }
                node = byId[String(node.parent_id)];
                guard++;
            }
            return chain; // chain[0]=کل ، chain[1]=معین ، chain[2]=تفصیل
        }

        // پیش‌انتخاب یک ردیف بر اساس data-selected (حالت ویرایش / old()).
        function initRow($wrap) {
            var selected = $wrap.attr('data-selected');
            var $kol = $wrap.find('.account-cascader-kol');
            var $moein = $wrap.find('.account-cascader-moein');
            var $tafsil = $wrap.find('.account-cascader-tafsil');

            if (!selected) {
                return;
            }

            var chain = ancestry(selected);
            if (!chain.length) {
                return;
            }

            $kol.val(String(chain[0].id));
            refresh($kol);

            if (chain[1]) {
                fill($moein, chain[0].id, 'حساب معین');
                $moein.prop('disabled', false);
                $moein.val(String(chain[1].id));
                refresh($moein);

                if (chain[2]) {
                    fill($tafsil, chain[1].id, 'حساب تفصیل');
                    $tafsil.prop('disabled', false);
                    $tafsil.val(String(chain[2].id));
                    refresh($tafsil);
                } else {
                    // حساب معین ممکن است خودش فرزند تفصیل داشته باشد؛ آن‌ها را بارگذاری کن ولی انتخابی اعمال نکن.
                    fill($tafsil, chain[1].id, 'حساب تفصیل');
                    $tafsil.prop('disabled', (byParent[String(chain[1].id)] || []).length === 0);
                    refresh($tafsil);
                }
            } else {
                // فقط کل انتخاب شده؛ معین‌ها را برای ادامهٔ کاربر آماده کن.
                fill($moein, chain[0].id, 'حساب معین');
                $moein.prop('disabled', (byParent[String(chain[0].id)] || []).length === 0);
                refresh($moein);
            }

            syncHidden($wrap);
        }

        // ریست یک ردیف تازه‌کلون‌شده.
        function resetRow($wrap) {
            $wrap.attr('data-selected', '');
            var $kol = $wrap.find('.account-cascader-kol');
            var $moein = $wrap.find('.account-cascader-moein');
            var $tafsil = $wrap.find('.account-cascader-tafsil');

            $kol.val('');
            refresh($kol);
            fill($moein, '__none__', 'حساب معین');
            $moein.prop('disabled', true);
            refresh($moein);
            fill($tafsil, '__none__', 'حساب تفصیل');
            $tafsil.prop('disabled', true);
            refresh($tafsil);
            $wrap.find('.account-cascader-id').val('');
        }

        function initAll(scope) {
            $(scope || document).find('.account-cascader').each(function () {
                initRow($(this));
            });
        }

        var bound = false;
        function bindDelegates() {
            if (bound) {
                return;
            }
            bound = true;

            $(document).on('change', '.account-cascader .account-cascader-kol', function () {
                var $wrap = $(this).closest('.account-cascader');
                var value = $(this).val();
                var $moein = $wrap.find('.account-cascader-moein');
                var $tafsil = $wrap.find('.account-cascader-tafsil');

                fill($moein, value || '__none__', 'حساب معین');
                $moein.prop('disabled', !value || (byParent[String(value)] || []).length === 0);
                refresh($moein);

                fill($tafsil, '__none__', 'حساب تفصیل');
                $tafsil.prop('disabled', true);
                refresh($tafsil);

                syncHidden($wrap);
            });

            $(document).on('change', '.account-cascader .account-cascader-moein', function () {
                var $wrap = $(this).closest('.account-cascader');
                var value = $(this).val();
                var $tafsil = $wrap.find('.account-cascader-tafsil');

                fill($tafsil, value || '__none__', 'حساب تفصیل');
                $tafsil.prop('disabled', !value || (byParent[String(value)] || []).length === 0);
                refresh($tafsil);

                syncHidden($wrap);
            });

            $(document).on('change', '.account-cascader .account-cascader-tafsil', function () {
                syncHidden($(this).closest('.account-cascader'));
            });
        }

        bindDelegates();

        return {
            initAll: initAll,
            initRow: initRow,
            resetRow: resetRow,
        };
    })();
</script>
