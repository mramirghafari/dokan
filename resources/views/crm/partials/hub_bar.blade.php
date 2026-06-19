@php
    $hubActive = $hubActive ?? '';
@endphp
<div class="card mb-4 border-0 shadow-sm" id="crm-tour-hub-bar">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('crm.workbench.index') }}" class="btn btn-sm {{ $hubActive === 'workbench' ? 'btn-primary' : 'btn-label-primary' }}">
                    <x-ui.icon name="layout-dashboard" class="me-1" />کارتابل
                </a>
                <a href="{{ route('crm.sales-boards.index') }}" class="btn btn-sm {{ $hubActive === 'boards' ? 'btn-primary' : 'btn-label-primary' }}">
                    <x-ui.icon name="layout-kanban" class="me-1" />کاریز
                </a>
                <a href="{{ route('crm.dashboard.index') }}" class="btn btn-sm {{ $hubActive === 'dashboard' ? 'btn-primary' : 'btn-label-primary' }}">
                    <x-ui.icon name="chart-dots" class="me-1" />داشبورد
                </a>
                <a href="{{ route('crm.followups.index') }}" class="btn btn-sm {{ $hubActive === 'followups' ? 'btn-primary' : 'btn-label-secondary' }}">پیگیری‌ها</a>
                <a href="{{ route('crm.opportunities.index') }}" class="btn btn-sm {{ $hubActive === 'opportunities' ? 'btn-primary' : 'btn-label-secondary' }}">فرصت‌ها</a>
                <a href="{{ route('crm.leads.index') }}" class="btn btn-sm {{ $hubActive === 'leads' ? 'btn-primary' : 'btn-label-secondary' }}">سرنخ‌ها</a>
                <a href="{{ route('crm.campaigns.index') }}" class="btn btn-sm {{ ($hubActive ?? '') === 'campaigns' ? 'btn-primary' : 'btn-label-secondary' }}">کمپین</a>
                <a href="{{ route('customers.index') }}" class="btn btn-sm btn-label-secondary">مشتریان</a>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#crmQuickFollowupModal">
                    <x-ui.icon name="plus" class="me-1" />پیگیری سریع
                </button>
                <a href="{{ route('crm.sales-boards.index') }}#createBoardModal" class="btn btn-sm btn-outline-primary">
                    <x-ui.icon name="columns" class="me-1" />بورد جدید
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="crmQuickFollowupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('crm.quick.followup') }}">
            @csrf
            <input type="hidden" name="redirect" value="{{ url()->current() }}">
            <div class="modal-header">
                <h5 class="modal-title">پیگیری سریع مشتری</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">مشتری</label>
                    <x-erp-remote-select entity="customers" name="customer_id" placeholder="جستجوی نام یا موبایل..." />
                </div>
                <div class="mb-3">
                    <label class="form-label">عنوان</label>
                    <input class="form-control" name="title" required maxlength="180" placeholder="مثلا تماس پیگیری قیمت">
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">موعد</label>
                        <input type="date" class="form-control" name="due_date_en" value="{{ now()->toDateString() }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">اولویت</label>
                        <select name="priority" class="form-select">
                            <option value="normal">عادی</option>
                            <option value="high">مهم</option>
                            <option value="urgent">فوری</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">یادداشت</label>
                    <textarea class="form-control" name="description" rows="2" placeholder="اختیاری"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="submit" class="btn btn-primary">ثبت پیگیری</button>
            </div>
        </form>
    </div>
</div>
