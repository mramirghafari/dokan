<link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
<script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
<script>
    function jalaliToGregorianYmd(jy, jm, jd) {
        jy = Number(jy);
        jm = Number(jm);
        jd = Number(jd);
        var gy = (jy <= 979) ? 621 : 1600;
        jy -= (jy <= 979) ? 0 : 979;
        var days = (365 * jy) + ((parseInt(jy / 33)) * 8) + (parseInt(((jy % 33) + 3) / 4)) +
            78 + jd + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
        gy += 400 * (parseInt(days / 146097));
        days %= 146097;
        if (days > 36524) {
            gy += 100 * (parseInt(--days / 36524));
            days %= 36524;
            if (days >= 365) days++;
        }
        gy += 4 * (parseInt((days) / 1461));
        days %= 1461;
        gy += parseInt((days - 1) / 365);
        if (days > 365) days = (days - 1) % 365;
        var gd = days + 1;
        var sal_a = [0, 31, ((gy % 4 == 0 && gy % 100 != 0) || (gy % 400 == 0)) ? 29 : 28, 31, 30, 31, 30,
            31, 31, 30, 31, 30, 31
        ];
        var gm;
        for (gm = 0; gm < 13; gm++) {
            var v = sal_a[gm];
            if (gd <= v) break;
            gd -= v;
        }
        return gy + '-' + String(gm).padStart(2, '0') + '-' + String(gd).padStart(2, '0');
    }

    function syncVoucherDateMiladi(jalaliInput) {
        var miladiInput = document.getElementById(jalaliInput.getAttribute('data-jdp-miladi-input'));
        if (!miladiInput) return;
        if (!jalaliInput.value) {
            miladiInput.value = '';
            return;
        }
        var date = jalaliInput.value.split('/');
        miladiInput.value = jalaliToGregorianYmd(date[0], date[1], date[2]);
    }

    document.addEventListener('DOMContentLoaded', function() {
        jalaliDatepicker.startWatch();
        document.querySelectorAll('[data-jdp-miladi-input]').forEach(function(input) {
            input.addEventListener('jdp:change', function() {
                syncVoucherDateMiladi(input);
            });
        });
    });
</script>
