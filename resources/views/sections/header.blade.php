@php
    use Hekmatinasser\Verta\Verta;

    $MyNotifs = DB::table('notifs')
        ->where('user_id', auth()->user()->id)
        ->where('status', 0)
        ->orderByDesc('id')
        ->limit(15)
        ->get();

    $availablePanels = collect($availablePanels ?? []);
    $activePanel = $activePanel ?? null;
    $userSideLabel = $userSideLabel ?? '';
    $canSwitchPanels = $availablePanels->count() > 1;
@endphp

<!-- Navbar -->
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <x-ui.icon name="menu-2" class="ti-sm" />
        </a>
    </div>

    <div class="navbar-header-toolbar d-flex align-items-center justify-content-between flex-grow-1 w-100">
        {{-- سمت راست: پروفایل کاربر + سوییچ پنل --}}
        <ul class="navbar-nav flex-row align-items-center gap-1">
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow navbar-user-link" data-bs-toggle="dropdown"
                    href="javascript:void(0);">
                    <div class="avatar avatar-online navbar-user-avatar">
                        <img alt="{{ $user->name }}" class="h-auto rounded-circle navbar-user-avatar-img"
                            src="{{ asset('assets/img/avatars/user-placeholder.svg') }}" />
                    </div>
                    <div class="navbar-user-meta">
                        <span class="fw-semibold navbar-user-name">{{ $user->name }}</span>
                        @if ($userSideLabel !== '')
                            <small class="text-muted navbar-user-side">{{ $userSideLabel }}</small>
                        @endif
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-start">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.change.get') }}">
                            <x-ui.icon name="user-check" class="me-2 ti-sm" />
                            <span class="align-middle">پروفایل من</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="pages-account-settings-account.html">
                            <x-ui.icon name="settings" class="me-2 ti-sm" />
                            <span class="align-middle">سابقه عملیات</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item profile-logout-btn">
                                <x-ui.icon name="fa-sign-out" class="profile-logout-icon" />
                                <span class="profile-logout-label">خروج از سیستم</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>

            @if ($canSwitchPanels)
                <li class="nav-item navbar-dropdown dropdown-panel-switch dropdown navbar-header-panel-section">
                    <a class="nav-link dropdown-toggle hide-arrow navbar-panel-switch-link" data-bs-toggle="dropdown"
                        href="javascript:void(0);">
                        <x-ui.icon name="building-store" class="ti-sm ms-1" />
                        <span class="navbar-panel-switch-label">{{ $activePanel['tenant_name'] ?? 'انتخاب پنل' }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-start panel-switch-menu">
                        <li class="dropdown-header panel-switch-menu__header">تغییر پنل</li>
                        @foreach ($availablePanels as $panel)
                            @php
                                $isActivePanel = ($activePanel['tenant_id'] ?? null) == $panel['tenant_id'];
                            @endphp
                            <li>
                                <form action="{{ route('panel.switch') }}" method="POST" class="mb-0">
                                    @csrf
                                    <input type="hidden" name="tenant_id" value="{{ $panel['tenant_id'] }}">
                                    <button type="submit"
                                        class="dropdown-item panel-switch-menu__item d-flex justify-content-between align-items-center @if($isActivePanel) active @endif"
                                        @if ($isActivePanel) aria-current="true" @endif>
                                        <span class="panel-switch-menu__text">
                                            <span class="d-block fw-semibold panel-switch-menu__name">{{ $panel['tenant_name'] }}</span>
                                            <small class="panel-switch-menu__role">{{ $panel['role_label'] }}</small>
                                        </span>
                                        @if ($isActivePanel)
                                            <x-ui.icon name="check" class="ti-sm panel-switch-menu__check" />
                                        @endif
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @elseif ($activePanel)
                <li class="nav-item d-none d-md-flex align-items-center navbar-current-panel navbar-header-panel-section text-muted">
                    <x-ui.icon name="building-store" class="ti-sm ms-1" />
                    <span>{{ $activePanel['tenant_name'] }}</span>
                </li>
            @endif
        </ul>

        {{-- سمت چپ: تاریخ امروز + اعلانات --}}
        <div class="navbar-header-meta d-flex align-items-center gap-2 gap-xl-3">
            <div class="navbar-today-date text-muted d-none d-sm-flex align-items-center gap-1">
                <x-ui.icon name="calendar" class="ti-sm" />
                <span>{{ Verta::now()->format('l، %d %B %Y') }}</span>
            </div>

            <ul class="navbar-nav flex-row align-items-center">
                <li class="nav-item">
                    <a class="nav-link hide-arrow" href="javascript:void(0);" id="panel-tour-trigger"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="تور راهنمای این صفحه">
                        <x-ui.icon name="route" class="ti-md" />
                    </a>
                </li>
                <li class="nav-item dropdown-notifications navbar-dropdown dropdown">
                    <a aria-expanded="false" class="nav-link dropdown-toggle hide-arrow" data-bs-auto-close="outside"
                        data-bs-toggle="dropdown" href="javascript:void(0);">
                        <x-ui.icon name="bell" class="ti-md" />
                        @if (count($MyNotifs) > 0)
                            <span class="badge bg-danger rounded-pill badge-notifications">{{ count($MyNotifs) }}</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-notifications-menu py-0">
                        <li class="dropdown-menu-header border-bottom">
                            <div class="dropdown-header d-flex align-items-center py-3">
                                <h5 class="text-body mb-0 me-auto">اعلانات</h5>
                                <form action="{{ route('notifications.readAll') }}" method="POST" class="mb-0">
                                    @csrf
                                    <button class="dropdown-notifications-all text-body border-0 bg-transparent p-0"
                                        data-bs-placement="top" data-bs-toggle="tooltip" title="همه خوانده شده"
                                        type="submit">
                                        <x-ui.icon name="mail-opened" class="fs-4" />
                                    </button>
                                </form>
                            </div>
                        </li>
                        <li class="dropdown-notifications-list scrollable-container">
                            <ul class="list-group list-group-flush">
                                @foreach ($MyNotifs as $notif)
                                    <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar">
                                                    <img alt class="h-auto rounded-circle"
                                                        src="{{ asset('assets/') }}/img/avatars/1.png" />
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-2">{{ $notif->title }}</h6>
                                                <p class="mb-1">
                                                    {{ \Illuminate\Support\Str::limit($notif->content, 90) }}</p>
                                                <small
                                                    class="text-muted">{{ $notif->created_at ? \Carbon\Carbon::parse($notif->created_at)->diffForHumans() : 'جدید' }}</small>
                                            </div>
                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                <form action="{{ route('notifications.read', $notif->id) }}"
                                                    method="POST" class="mb-0">
                                                    @csrf
                                                    <button
                                                        class="dropdown-notifications-read border-0 bg-transparent p-0"
                                                        title="خوانده شد" type="submit">
                                                        <span class="badge badge-dot"></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach

                                @if (count($MyNotifs) === 0)
                                    <li class="list-group-item text-center text-muted py-4">اعلان خوانده نشده ندارید.
                                    </li>
                                @endif
                            </ul>
                        </li>
                        <li class="dropdown-menu-footer border-top">
                            <a class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center"
                                href="#">
                                نمایش همه اعلانات
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- / Navbar -->
@include('partials.panel-toasts')
@include('partials.panel-tour-assets')
@include('partials.panel-tour-scripts')
