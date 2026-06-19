@if (!empty($panelOnboarding['show_welcome_modal']))
    <div class="modal fade" id="panelWelcomeModal" tabindex="-1" aria-labelledby="panelWelcomeModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-panel-welcome-show="1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="ob-modal__head">
                    <button type="button" class="ob-modal__close" aria-label="بستن" id="panel-welcome-close-btn">
                        <x-ui.icon name="x" />
                    </button>
                    <span class="ob-modal__eyebrow"><x-ui.icon name="sparkles" /> پنل جدید</span>
                    <div class="ob-modal__icon"><x-ui.icon name="confetti" /></div>
                    <h4 class="ob-modal__title" id="panelWelcomeModalLabel">
                        به {{ $panelOnboarding['panel_name'] ?? 'پنل جدید' }} خوش آمدید
                    </h4>
                </div>
                <div class="ob-modal__body">
                    <p class="ob-modal__text">
                        به سامانهٔ یکپارچهٔ دکان ERP خوش آمدید. این داشبورد مرکز مدیریت کسب‌وکار شماست —
                        از تیم و محصولات تا فروش، انبار، مالی و گزارش‌ها. کارت راه‌اندازی مراحل باقیمانده را
                        گام‌به‌گام پیش می‌برد؛ با تور آموزشی هم بخش‌های کلیدی را سریع می‌شناسید.
                    </p>
                    <ul class="ob-modal__features">
                        <li><x-ui.icon name="check" /> راهنمای گام‌به‌گام تنظیمات پنل</li>
                        <li><x-ui.icon name="check" /> تور کوتاه برای آشنایی با داشبورد</li>
                        <li><x-ui.icon name="check" /> پوشش تیم، محصولات، عملیات و گزارش‌ها</li>
                    </ul>
                </div>
                <div class="ob-modal__foot">
                    <button type="button" class="btn btn-label-secondary flex-fill" id="panel-welcome-skip-btn">بعداً</button>
                    <button type="button" class="btn btn-primary flex-fill" id="panel-welcome-tour-btn">
                        <x-ui.icon name="route" class="me-1" /> شروع تور
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
