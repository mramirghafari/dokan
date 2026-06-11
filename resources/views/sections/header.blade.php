<!-- Navbar -->
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-sm"></i>
        </a>
    </div>
    <div class="navbar-nav-left d-flex align-items-center" id="navbar-collapse">
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" data-bs-toggle="dropdown" href="javascript:void(0);"
                    style="display: flex;gap: 20px;">
                    <div class="avatar avatar-online">
                        <img alt class="h-auto rounded-circle" src="{{ asset('assets/') }}/img/avatars/1.png" />
                    </div>
                    <div class="flex-grow-1">
                        <span class="fw-semibold d-block mb-1">{{ $user->name }}</span>
                        <small class="text-muted">
                            @foreach ($user->roles as $role)
                                {{ $role->description }} -
                            @endforeach
                        </small>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-start">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.change.get') }}">
                            <i class="ti ti-user-check me-2 ti-sm"></i>
                            <span class="align-middle">پروفایل من</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="pages-account-settings-account.html">
                            <i class="ti ti-settings me-2 ti-sm"></i>
                            <span class="align-middle">سابقه عملیات</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item"><i
                                    class="fa fa-sign-out profile-icon bg-danger" aria-hidden="true"></i>
                                خروج از سیستم</button>
                        </form>
                    </li>
                </ul>
            </li>
            <!--/ User -->
            <!-- Notification -->
            <?php
            $MyNotifs = DB::table('notifs')
                ->where('user_id', auth()->user()->id)
                ->where('status', 0)
                ->orderByDesc('id')
                ->limit(15)
                ->get();
            ?>
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                <a aria-expanded="false" class="nav-link dropdown-toggle hide-arrow" data-bs-auto-close="outside"
                    data-bs-toggle="dropdown" href="javascript:void(0);">
                    <i class="ti ti-bell ti-md"></i>
                    @if (count($MyNotifs) > 0)
                        <span class="badge bg-danger rounded-pill badge-notifications">{{ count($MyNotifs) }}</span>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-start py-0">
                    <li class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h5 class="text-body mb-0 me-auto">اعلانات</h5>
                            <form action="{{ route('notifications.readAll') }}" method="POST" class="mb-0">
                                @csrf
                                <button class="dropdown-notifications-all text-body border-0 bg-transparent p-0"
                                    data-bs-placement="top" data-bs-toggle="tooltip" title="همه خوانده شده"
                                    type="submit">
                                    <i class="ti ti-mail-opened fs-4"></i>
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
                                            <p class="mb-1">{{ \Illuminate\Support\Str::limit($notif->content, 90) }}
                                            </p>
                                            <small
                                                class="text-muted">{{ $notif->created_at ? \Carbon\Carbon::parse($notif->created_at)->diffForHumans() : 'جدید' }}</small>
                                        </div>
                                        <div class="flex-shrink-0 dropdown-notifications-actions">
                                            <form action="{{ route('notifications.read', $notif->id) }}" method="POST"
                                                class="mb-0">
                                                @csrf
                                                <button class="dropdown-notifications-read border-0 bg-transparent p-0"
                                                    title="خوانده شد" type="submit">
                                                    <span class="badge badge-dot"></span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach

                            @if (count($MyNotifs) === 0)
                                <li class="list-group-item text-center text-muted py-4">اعلان خوانده نشده ندارید.</li>
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
            <!--/ Notification -->

        </ul>
    </div>
</nav>
<!-- / Navbar -->
