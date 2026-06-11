<?php
$Visitor_per = DB::table('roles')->where('title', 'visitor')->first();
$IsVisitor = $Visitor_per
    ? DB::table('role_user')
        ->where('role_id', $Visitor_per->id)
        ->where('user_id', auth()->user()->id)
        ->count()
    : 0;

$Leader_per = DB::table('roles')->where('title', 'leader')->first();
$IsLeader = $Leader_per
    ? DB::table('role_user')
        ->where('role_id', $Leader_per->id)
        ->where('user_id', auth()->user()->id)
        ->count()
    : 0;

$Agent_per = DB::table('roles')->where('title', 'agent')->first();
$IsAgent = $Agent_per
    ? DB::table('role_user')
        ->where('role_id', $Agent_per->id)
        ->where('user_id', auth()->user()->id)
        ->count()
    : 0;

$panelSettings = \App\Services\TenantSettings::all();
$featureAreaManagement = ($panelSettings['feature_area_management'] ?? 'yes') === 'yes';
$featureBranchManagement = ($panelSettings['feature_branch_management'] ?? 'yes') === 'yes';
$featureCityManagement = ($panelSettings['feature_city_management'] ?? 'yes') === 'yes';
$featureRouteManagement = ($panelSettings['feature_route_management'] ?? 'yes') === 'yes';
$featureSalesTargets = ($panelSettings['feature_sales_targets'] ?? 'yes') === 'yes';
$featureDistribution = ($panelSettings['feature_distribution'] ?? 'yes') === 'yes';
$featureMultiWarehouse = ($panelSettings['feature_multi_warehouse'] ?? 'yes') === 'yes';
$featureWarehouseManagement = ($panelSettings['feature_warehouse_management'] ?? 'yes') === 'yes';
$featureMultiPrice = ($panelSettings['feature_multi_price'] ?? 'yes') === 'yes';
$featureManagerOrderApproval = ($panelSettings['feature_manager_order_approval'] ?? 'no') === 'yes';
$navigationOrder = \App\Services\TenantSettings::get('navigation_menu_order', null, []);
$navigationOrder = is_array($navigationOrder) ? $navigationOrder : [];
$navigationItems = (array) config('panel_navigation.items', []);
$navigationCss = [];

foreach ($navigationItems as $navigationKey => $navigationItem) {
    $navigationCss[$navigationKey] = (int) ($navigationOrder[$navigationKey] ?? ($navigationItem['default_order'] ?? 1000));
}
?>
<style>
    .menu-inner {
        display: flex;
        flex-direction: column;
    }

    @foreach ($navigationCss as $navigationKey => $navigationPosition)
        .menu-inner>[data-menu-key="{{ $navigationKey }}"] {
            order: {{ $navigationPosition }};
        }
    @endforeach

    ul.sub-menu {
        margin-right: 25px;
        list-style-type: circle;
    }
</style>

<!-- Menu -->
<aside class="layout-menu menu-vertical menu bg-menu-theme" id="layout-menu">
    <div class="app-brand demo">
        <a class="app-brand-link" href="{{ asset('/') }}">
            <span class="app-brand-logo demo">
                <svg width="40" height="40" viewBox="0 0 76 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M31.8349 0C31.8349 0 31.8349 0.0193197 31.6997 0.0193197H31.5258C27.7391 0.135238 24.6866 3.20708 24.6866 7.03239C24.6866 10.8577 27.7391 13.9295 31.5258 14.0455H31.6224C31.6224 14.0455 31.719 14.0455 31.7769 14.0455C31.7769 14.0455 31.7963 14.0455 31.8156 14.0455C31.8349 14.0455 31.8542 14.0455 31.8735 14.0455C48.4692 14.0648 61.8191 27.8784 61.8191 44.8604C61.8191 61.8425 48.5851 75.5982 32.0088 75.6368C31.9701 75.6368 31.9122 75.6368 31.8735 75.6368C31.8542 75.6368 31.8349 75.6368 31.7963 75.6368C31.7576 75.6368 31.719 75.6368 31.6803 75.6368H31.5065C27.7005 75.7527 24.6479 78.8246 24.6479 82.6692C24.6479 86.5138 27.7198 89.5857 31.5065 89.6823H31.7963C31.7963 89.6823 31.7963 89.6823 31.8542 89.6823C31.8542 89.6823 31.8542 89.6823 31.8735 89.6823C56.3323 89.6823 75.9998 69.609 75.9998 44.8411C75.9998 20.0732 56.313 0 31.8349 0Z"
                        fill="#524595" />
                    <path
                        d="M14.1116 20.2874C14.0923 16.4234 10.9432 13.2936 7.07926 13.2936C3.21531 13.2936 0.0661947 16.4234 0.046875 20.2874V69.3595C0.104834 73.2042 3.21531 76.3147 7.07926 76.3147C10.9432 76.3147 14.0537 73.2042 14.1116 69.3595V20.2874Z"
                        fill="#524595" />
                </svg>

            </span>
            <span class="app-brand-text demo menu-text fw-bold"> <img class="ms-3"
                    src="{{ asset('assets/') }}/img/logo-sidebar.png" /></span>
        </a>
        <a class="layout-menu-toggle menu-link text-large ms-auto" href="javascript:void(0);">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        <!-- Dashboards -->
        <li class="menu-item dokan {{ Request::routeIs(['index']) ? 'open' : '' }}" data-menu-key="dashboard">
            <a class="menu-link" href="{{ asset('/') }}">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0 3C0 2.20435 0.316071 1.44129 0.87868 0.87868C1.44129 0.316071 2.20435 0 3 0H5.25C6.04565 0 6.80871 0.316071 7.37132 0.87868C7.93393 1.44129 8.25 2.20435 8.25 3V5.25C8.25 6.04565 7.93393 6.80871 7.37132 7.37132C6.80871 7.93393 6.04565 8.25 5.25 8.25H3C2.20435 8.25 1.44129 7.93393 0.87868 7.37132C0.316071 6.80871 0 6.04565 0 5.25V3ZM9.75 3C9.75 2.20435 10.0661 1.44129 10.6287 0.87868C11.1913 0.316071 11.9544 0 12.75 0H15C15.7956 0 16.5587 0.316071 17.1213 0.87868C17.6839 1.44129 18 2.20435 18 3V5.25C18 6.04565 17.6839 6.80871 17.1213 7.37132C16.5587 7.93393 15.7956 8.25 15 8.25H12.75C11.9544 8.25 11.1913 7.93393 10.6287 7.37132C10.0661 6.80871 9.75 6.04565 9.75 5.25V3ZM0 12.75C0 11.9544 0.316071 11.1913 0.87868 10.6287C1.44129 10.0661 2.20435 9.75 3 9.75H5.25C6.04565 9.75 6.80871 10.0661 7.37132 10.6287C7.93393 11.1913 8.25 11.9544 8.25 12.75V15C8.25 15.7956 7.93393 16.5587 7.37132 17.1213C6.80871 17.6839 6.04565 18 5.25 18H3C2.20435 18 1.44129 17.6839 0.87868 17.1213C0.316071 16.5587 0 15.7956 0 15V12.75ZM9.75 12.75C9.75 11.9544 10.0661 11.1913 10.6287 10.6287C11.1913 10.0661 11.9544 9.75 12.75 9.75H15C15.7956 9.75 16.5587 10.0661 17.1213 10.6287C17.6839 11.1913 18 11.9544 18 12.75V15C18 15.7956 17.6839 16.5587 17.1213 17.1213C16.5587 17.6839 15.7956 18 15 18H12.75C11.9544 18 11.1913 17.6839 10.6287 17.1213C10.0661 16.5587 9.75 15.7956 9.75 15V12.75Z"
                        fill="#1C1C1C" />
                </svg>
                <div>دکان</div>
            </a>
        </li>
        <li class="menu-item dokan {{ Request::routeIs(['products.collection']) ? 'open' : '' }}"
            data-menu-key="product_catalog">
            <a class="menu-link" href="{{ route('products.collection') }}">
                <svg width="18" height="19" viewBox="0 0 18 19" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M17 5.49967L9 0.833008L1 5.49967M17 5.49967L9 10.1663M17 5.49967V13.4997L9 18.1663M1 5.49967L9 10.1663M1 5.49967V13.4997L9 18.1663M9 10.1663V18.1663"
                        stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div>کاتالوگ محصولات</div>
            </a>
        </li>
        <!-- Layouts -->
        @canany(['brands', 'stores', 'categories', 'brands', 'units', 'organizations', 'cities', 'regions', 'areas',
            'factormanager', 'settings'])
            <li class="menu-item basicdata {{ Request::routeIs([
                'tenants.index',
                'tenants.edit',
                'tenants.trashed.get',
                'stores.index',
                'stores.edit',
                'stores.trashed.get',
                'warehouse-locations.index',
                'warehouse-locations.edit',
                'organizations.index',
                'organizations.edit',
                'categories.index',
                'categories.edit',
                'categories.trashed.get',
                'brands.index',
                'brands.edit',
                'brands.trashed.get',
                'cities.index',
                'cities.edit',
                'regions.index',
                'regions.edit',
                'areas.index',
                'areas.edit',
                'FactorManager.index',
                'settings.index',
                'settings.salesScenario',
                'Account.index',
                'Terminals.index',
            ])
                ? 'open'
                : '' }}"
                data-menu-key="basic_data">
                <a class="menu-link menu-toggle" href="javascript:void(0);">
                    <svg width="19" height="20" viewBox="0 0 19 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M8.99489 12.7607L14.0762 17.842C14.512 18.2639 15.0961 18.4975 15.7026 18.4926C16.3091 18.4877 16.8894 18.2446 17.3183 17.8157C17.7471 17.3868 17.9903 16.8066 17.9952 16.2001C18.0001 15.5935 17.7664 15.0094 17.3446 14.5736L12.2223 9.45138M8.99489 12.7607L11.1703 10.1199C11.4466 9.78519 11.8153 9.57427 12.2232 9.45225C12.7026 9.30931 13.2368 9.28839 13.7424 9.33023C14.4238 9.38874 15.1087 9.26805 15.729 8.98013C16.3494 8.69221 16.8837 8.24707 17.2789 7.68888C17.6741 7.1307 17.9165 6.47887 17.982 5.79808C18.0475 5.1173 17.9338 4.43122 17.6522 3.80793L14.797 6.66408C14.3193 6.55362 13.8822 6.31124 13.5355 5.96453C13.1888 5.61783 12.9464 5.18074 12.8359 4.70304L15.6912 1.84776C15.0679 1.56621 14.3818 1.45254 13.701 1.51803C13.0203 1.58352 12.3684 1.82589 11.8102 2.2211C11.2521 2.6163 10.8069 3.1506 10.519 3.77097C10.2311 4.39134 10.1104 5.07622 10.1689 5.75764C10.2482 6.69546 10.107 7.73089 9.381 8.32879L9.2921 8.40287M8.99489 12.7607L4.93771 17.6878C4.74108 17.9274 4.49643 18.1233 4.21953 18.2627C3.94263 18.4022 3.6396 18.4821 3.32994 18.4973C3.02029 18.5126 2.71088 18.4628 2.42162 18.3512C2.13236 18.2397 1.86967 18.0688 1.65045 17.8496C1.43123 17.6303 1.26034 17.3676 1.14877 17.0784C1.03721 16.7891 0.987445 16.4797 1.00269 16.1701C1.01793 15.8604 1.09783 15.5574 1.23726 15.2805C1.37669 15.0036 1.57255 14.7589 1.81224 14.5623L7.7712 9.65532L4.19164 6.07577H2.96359L1.00255 2.80736L2.30991 1.5L5.57832 3.46104V4.68909L9.29123 8.402L7.77033 9.65445M15.0567 15.5541L12.7688 13.2663M3.28346 16.2078H3.29043V16.2148H3.28346V16.2078Z"
                            stroke="black" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>


                    <div>اطلاعات پایه</div>
                </a>
                <ul class="menu-sub">
                    @if (auth()->user()->isGod == 1)
                        <li
                            class="menu-item panels {{ Request::routeIs(['tenants.index', 'tenants.edit', 'tenants.trashed.get']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('tenants.index') }}">
                                <div>پنل ها</div>
                            </a>
                        </li>
                    @endif
                    @if ($featureWarehouseManagement)
                        @can('stores')
                            <li
                                class="menu-item stores {{ Request::routeIs(['stores.index', 'stores.edit', 'stores.trashed.get']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('stores.index') }}">
                                    <div>لیست انبار ها</div>
                                </a>
                            </li>
                            <li
                                class="menu-item warehouse_locations {{ Request::routeIs(['warehouse-locations.index', 'warehouse-locations.edit']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('warehouse-locations.index') }}">
                                    <div>قفسه و مکان انبار</div>
                                </a>
                            </li>
                        @endcan
                    @endif
                    @if ($featureBranchManagement)
                        @can('organizations')
                            <li
                                class="menu-item organizations {{ Request::routeIs(['organizations.index', 'organizations.edit']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('organizations.index') }}">
                                    <div>واحدهای پخش</div>
                                </a>
                            </li>
                        @endcan
                    @endif

                    @can('categories')
                        <li
                            class="menu-item categories {{ Request::routeIs(['categories.index', 'categories.edit', 'categories.trashed.get']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('categories.index') }}">
                                <div>دسته بندی ها</div>
                            </a>
                        </li>
                    @endcan

                    @can('brands')
                        <li
                            class="menu-item brands {{ Request::routeIs(['brands.index', 'brands.edit', 'brands.trashed.get']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('brands.index') }}">
                                <div>برند ها</div>
                            </a>
                        </li>
                    @endcan

                    @if ($featureCityManagement)
                        @can('cities')
                            <li
                                class="menu-item cities {{ Request::routeIs(['cities.index', 'cities.edit']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('cities.index') }}">
                                    <div>شهرها</div>
                                </a>
                            </li>
                        @endcan
                    @endif
                    @if ($featureAreaManagement)
                        @can('regions')
                            <li
                                class="menu-item regions {{ Request::routeIs(['regions.index', 'regions.edit']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('regions.index') }}">
                                    <div>مناطق</div>
                                </a>
                            </li>
                        @endcan
                    @endif
                    @if ($featureRouteManagement)
                        @can('areas')
                            <li class="menu-item areas {{ Request::routeIs(['areas.index', 'areas.edit']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('areas.index') }}">
                                    <div>مسیر ها</div>
                                </a>
                            </li>
                        @endcan
                    @endif
                    @can('factormanager')
                        <li class="menu-item factormaker">
                            <a class="menu-link" href="{{ route('FactorManager.index') }}">
                                <div>تنظیمات فاکتور</div>
                            </a>
                        </li>
                    @endcan
                    @can('settings')
                        <li class="menu-item settings {{ Request::routeIs(['settings.index']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('settings.index') }}">
                                <div>تنظیمات پنل</div>
                            </a>
                        </li>
                        <li
                            class="menu-item sales-scenario {{ Request::routeIs(['settings.salesScenario']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('settings.salesScenario') }}">
                                <div>سناریوی فروش</div>
                            </a>
                        </li>
                        @if (auth()->user()->isGod == 1)
                            <li
                                class="menu-item notification-settings {{ Request::routeIs(['settings.notifications']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('settings.notifications') }}">
                                    <div>اعلانات و پیامک ها</div>
                                </a>
                            </li>
                        @endif
                    @endcan
                    @can('accountManager')
                        <li class="menu-item accounts">
                            <a class="menu-link" href="{{ route('Account.index') }}">
                                <div>حساب ها</div>
                            </a>
                        </li>
                        <li class="menu-item terminals">
                            <a class="menu-link" href="{{ route('Terminals.index') }}">
                                <div>پایانه ها / درگاه ها</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany
        <!-- Front Pages -->
        @canany(['roles', 'permissions', 'users'])
            <li class="menu-item users {{ Request::routeIs(['users.index', 'users.edit', 'users.update', 'users.trashed.get']) ? 'open' : '' }}"
                data-menu-key="users">
                <a class="menu-link menu-toggle" href="javascript:void(0);">
                    <svg width="20" height="18" viewBox="0 0 20 18" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M14.7347 11.918C15.1686 11.6217 15.6775 11.4543 16.2026 11.4352C16.7277 11.416 17.2474 11.546 17.7017 11.8099C18.1561 12.0738 18.5264 12.461 18.7698 12.9266C19.0133 13.3922 19.1199 13.9172 19.0774 14.4409C17.9646 14.8299 16.7825 14.9812 15.6075 14.8852C15.6039 13.834 15.3012 12.8045 14.7347 11.9189C14.2319 11.1303 13.5383 10.4813 12.7181 10.0319C11.8979 9.58246 10.9776 9.34718 10.0423 9.34782C9.10722 9.34734 8.18709 9.58269 7.36706 10.0321C6.54703 10.4815 5.85358 11.1305 5.35085 11.9189M15.6066 14.8843L15.6075 14.913C15.6075 15.1217 15.5964 15.3276 15.5732 15.5308C13.8901 16.4964 11.9828 17.0031 10.0423 17C8.02957 17 6.14018 16.4657 4.51143 15.5308C4.48758 15.3161 4.47613 15.1003 4.47711 14.8843M4.47711 14.8843C3.30251 14.9838 2.12109 14.833 1.00906 14.4418C0.966716 13.9183 1.07342 13.3935 1.31684 12.928C1.56026 12.4626 1.93044 12.0755 2.38459 11.8116C2.83875 11.5477 3.35832 11.4178 3.88323 11.4367C4.40814 11.4557 4.91696 11.6229 5.35085 11.9189M4.47711 14.8843C4.48044 13.8331 4.7847 12.8046 5.35085 11.9189M12.8249 3.78261C12.8249 4.5206 12.5318 5.22837 12.0099 5.75021C11.4881 6.27205 10.7803 6.56521 10.0423 6.56521C9.30433 6.56521 8.59656 6.27205 8.07472 5.75021C7.55288 5.22837 7.25972 4.5206 7.25972 3.78261C7.25972 3.04461 7.55288 2.33685 8.07472 1.81501C8.59656 1.29317 9.30433 1 10.0423 1C10.7803 1 11.4881 1.29317 12.0099 1.81501C12.5318 2.33685 12.8249 3.04461 12.8249 3.78261ZM18.3901 6.56521C18.3901 6.83928 18.3362 7.11065 18.2313 7.36386C18.1264 7.61706 17.9727 7.84712 17.7789 8.04091C17.5851 8.2347 17.355 8.38843 17.1018 8.49331C16.8486 8.59819 16.5773 8.65217 16.3032 8.65217C16.0291 8.65217 15.7577 8.59819 15.5045 8.49331C15.2513 8.38843 15.0213 8.2347 14.8275 8.04091C14.6337 7.84712 14.48 7.61706 14.3751 7.36386C14.2702 7.11065 14.2162 6.83928 14.2162 6.56521C14.2162 6.01172 14.4361 5.48089 14.8275 5.08951C15.2189 4.69813 15.7497 4.47826 16.3032 4.47826C16.8567 4.47826 17.3875 4.69813 17.7789 5.08951C18.1703 5.48089 18.3901 6.01172 18.3901 6.56521ZM5.86842 6.56521C5.86842 6.83928 5.81443 7.11065 5.70956 7.36386C5.60468 7.61706 5.45095 7.84712 5.25716 8.04091C5.06337 8.2347 4.8333 8.38843 4.5801 8.49331C4.3269 8.59819 4.05552 8.65217 3.78146 8.65217C3.5074 8.65217 3.23602 8.59819 2.98282 8.49331C2.72962 8.38843 2.49955 8.2347 2.30576 8.04091C2.11197 7.84712 1.95824 7.61706 1.85337 7.36386C1.74849 7.11065 1.69451 6.83928 1.69451 6.56521C1.69451 6.01172 1.91438 5.48089 2.30576 5.08951C2.69714 4.69813 3.22797 4.47826 3.78146 4.47826C4.33496 4.47826 4.86578 4.69813 5.25716 5.08951C5.64854 5.48089 5.86842 6.01172 5.86842 6.56521Z"
                            stroke="black" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    <div>کاربران</div>
                </a>
                <ul class="menu-sub">
                    @can('users')
                        <li
                            class="menu-item userslist {{ Request::routeIs(['users.index', 'users.edit', 'users.update', 'users.trashed.get']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('users.index') }}">
                                <div>کاربران سامانه</div>
                            </a>
                        </li>
                    @endcan

                    @can('roles')
                        <li
                            class="menu-item roles {{ Request::routeIs(['roles.index', 'roles.edit', 'roles.update', 'roles.trashed']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('roles.index') }}">
                                <div>نقش ها</div>
                            </a>
                        </li>
                    @endcan
                    @can('permissions')
                        <li
                            class="menu-item permisions {{ Request::routeIs(['permissions.index', 'permissions.edit', 'permissions.update', 'permissions.trashed']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('permissions.index') }}">
                                <div>سطوح دسترسی</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
        @if ($featureRouteManagement)
            @canany(['tasks-add', 'tasks'])
                <li class="menu-item tasks {{ Request::routeIs(['tasks.index', 'tasks.edit', 'tasks.update', 'tasks.trashed', 'tasks.create', 'tasks.active_list']) ? 'open' : '' }}"
                    data-menu-key="routes">
                    <a class="menu-link menu-toggle" href="javascript:void(0);">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.33333 4.21485V11.5482M11.6667 6.21485V13.5482M12.1138 16.6575L16.4471 14.4913C16.7858 14.3224 17 13.9757 17 13.5971V2.4993C17 1.75619 16.2178 1.27263 15.5529 1.60508L12.1138 3.32419C11.832 3.46552 11.5004 3.46552 11.2196 3.32419L6.78044 1.10552C6.64162 1.03613 6.48854 1 6.33333 1C6.17813 1 6.02505 1.03613 5.88622 1.10552L1.55289 3.27174C1.21333 3.44152 1 3.78819 1 4.16597V15.2637C1 16.0069 1.78222 16.4904 2.44711 16.158L5.88622 14.4389C6.168 14.2975 6.49956 14.2975 6.78044 14.4389L11.2196 16.6584C11.5013 16.7989 11.8329 16.7989 12.1138 16.6584V16.6575Z"
                                stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div>مسیرها</div>
                    </a>
                    <ul class="menu-sub">
                        @canany(['tasks-add'])
                            <li
                                class="menu-item add-task {{ Request::routeIs(['tasks.create', 'tasks.edit']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('tasks.create') }}">
                                    <div>ثبت مسیر برای بازاریاب</div>
                                </a>
                            </li>
                        @endcan
                        @canany(['tasks'])
                            <li class="menu-item active_list {{ Request::routeIs(['tasks.active_list']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('tasks.active_list') }}">
                                    <div>مسیرهای فعال</div>
                                </a>
                            </li>
                            <li class="menu-item history {{ Request::routeIs(['tasks.index']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('tasks.index') }}">
                                    <div>مرور مسیرها</div>
                                </a>
                            </li>
                        @endcan

                        @if ($IsVisitor == 1)
                            <li class="menu-item mytasks  {{ Request::routeIs(['tasks.MyTasks']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('tasks.MyTasks') }}">
                                    <div>مسیرهای من</div>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endcan
        @endif

        @canany(['product-add'])
            <li class="menu-item products {{ Request::routeIs(['products.index', 'products.edit', 'products.update', 'products.trashed', 'products.create']) ? 'open' : '' }}"
                data-menu-key="products">
                <a class="menu-link menu-toggle" href="javascript:void(0);">
                    <svg width="18" height="19" viewBox="0 0 18 19" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M17 5.66667L9 1L1 5.66667M17 5.66667L9 10.3333M17 5.66667V13.6667L9 18.3333M1 5.66667L9 10.3333M1 5.66667V13.6667L9 18.3333M9 10.3333V18.3333"
                            stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    <div>محصولات</div>
                </a>
                <ul class="menu-sub">
                    @can('product-add')
                        <li class="menu-item {{ Request::routeIs(['products.edit', 'products.create']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('products.create') }}">
                                <div>ثبت محصول جدید</div>
                            </a>
                        </li>
                    @endcan
                    @can('products')
                        <li
                            class="menu-item productslist {{ Request::routeIs(['products.index', 'products.trashed.get']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('products.index') }}">
                                <div>محصولات</div>
                            </a>
                        </li>
                    @endcan
                    @if ($featureMultiPrice)
                        @can('product-add')
                            <li class="menu-item fees {{ Request::routeIs(['products.updateFees']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('products.updateFees') }}">
                                    <div>قیمت روز</div>
                                </a>
                            </li>
                        @endcan
                    @endif

                    @can('materials')
                        <li
                            class="menu-item list  {{ Request::routeIs(['Materials.index', 'Materials.edit']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('Materials.index') }}">
                                <div>لیست مواد اولیه</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @if ($featureSalesTargets)
            @canany(['targets-add', 'targets', 'target-list'])
                <li class="menu-item targets {{ Request::routeIs(['targets.index', 'targets.edit', 'targets.update', 'targets.trashed', 'targets.create', 'targets.history', 'commissions.index']) ? 'open' : '' }}"
                    data-menu-key="targets">
                    <a class="menu-link menu-toggle" href="javascript:void(0);">
                        <svg width="22" height="22" viewBox="0 0 20 21" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M1 11.625C1 11.004 1.504 10.5 2.125 10.5H4.375C4.996 10.5 5.5 11.004 5.5 11.625V18.375C5.5 18.996 4.996 19.5 4.375 19.5H2.125C1.82663 19.5 1.54048 19.3815 1.3295 19.1705C1.11853 18.9595 1 18.6734 1 18.375V11.625ZM7.75 7.125C7.75 6.504 8.254 6 8.875 6H11.125C11.746 6 12.25 6.504 12.25 7.125V18.375C12.25 18.996 11.746 19.5 11.125 19.5H8.875C8.57663 19.5 8.29048 19.3815 8.0795 19.1705C7.86853 18.9595 7.75 18.6734 7.75 18.375V7.125ZM14.5 2.625C14.5 2.004 15.004 1.5 15.625 1.5H17.875C18.496 1.5 19 2.004 19 2.625V18.375C19 18.996 18.496 19.5 17.875 19.5H15.625C15.3266 19.5 15.0405 19.3815 14.8295 19.1705C14.6185 18.9595 14.5 18.6734 14.5 18.375V2.625Z"
                                stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>


                        <div>تارگت های فروش</div>
                    </a>
                    <ul class="menu-sub">
                        @canany(['targets-add'])
                            <li class="menu-item add-target {{ Request::routeIs(['targets.create']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('targets.create') }}">
                                    <div>ثبت تارگت جدید</div>
                                </a>
                            </li>
                        @endcan
                        @canany(['targets'])
                            <li
                                class="menu-item active_list {{ Request::routeIs(['targets.index', 'targets.edit']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('targets.index') }}">
                                    <div>تارگت های فعال</div>
                                </a>
                            </li>
                            <li class="menu-item history {{ Request::routeIs(['targets.history']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('targets.history') }}">
                                    <div>مرور تارگت ها</div>
                                </a>
                            </li>
                            <li class="menu-item commissions {{ Request::routeIs(['commissions.index']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('commissions.index') }}">
                                    <div>پورسانت و تسویه</div>
                                </a>
                            </li>
                        @endcan
                        <li class="menu-item my_targets  {{ Request::routeIs(['targets.MyTargets']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('targets.MyTargets') }}">
                                <div>تارگت پلن من</div>
                            </a>
                        </li>
                        <li
                            class="menu-item management_report {{ Request::routeIs(['reports.management', 'reports.management.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('reports.management') }}">
                                <div>گزارش مدیریتی</div>
                            </a>
                        </li>
                        <li
                            class="menu-item bi_dashboard {{ Request::routeIs(['bi.dashboard.*', 'bi.report-builder.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('bi.dashboard.index') }}">
                                <div>BI جامع</div>
                            </a>
                        </li>
                        <li class="menu-item bi_executive {{ Request::routeIs(['bi.executive.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('bi.executive.index') }}">
                                <div>داشبورد Executive</div>
                            </a>
                        </li>
                        <li class="menu-item bi_cfo {{ Request::routeIs(['bi.cfo.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('bi.cfo.index') }}">
                                <div>داشبورد CFO</div>
                            </a>
                        </li>
                        <li class="menu-item bi_reconciliation {{ Request::routeIs(['bi.reconciliation.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('bi.reconciliation.index') }}">
                                <div>مغایرت‌گیری BI</div>
                            </a>
                        </li>
                        <li class="menu-item bi_insights {{ Request::routeIs(['bi.insights.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('bi.insights.index') }}">
                                <div>هشدار و پیش بینی BI</div>
                            </a>
                        </li>
                        <li
                            class="menu-item erp_scale_hardening {{ Request::routeIs(['erp.scale-hardening.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('erp.scale-hardening.index') }}">
                                <div>سبک سازی و مقیاس ERP</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan
        @endif

        @canany(['customers-add', 'customers'])
            <li class="menu-item customers {{ Request::routeIs(['customers.index', 'customers.search', 'customers.edit', 'customers.update', 'customers.trashed', 'customers.create', 'customers.createdByMe', 'crm.dashboard.*', 'crm.health.*', 'crm.workbench.*', 'crm.followups.*', 'crm.leads.*', 'crm.service-tickets.*', 'crm.call-center.*', 'crm.campaigns.*', 'crm.loyalty.*', 'crm.customer-portal.*', 'crm.public-api.*', 'crm.integrations.*', 'crm.employee-performance.*', 'crm.opportunities.*', 'crm.sales-boards.*']) ? 'open' : '' }}"
                data-menu-key="customers">
                <a class="menu-link menu-toggle" href="javascript:void(0);">
                    <svg width="22" height="22" viewBox="0 0 19 19" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M11.3027 4.13995C11.3027 5.0284 10.9498 5.88046 10.3216 6.50869C9.69335 7.13692 8.84129 7.48986 7.95283 7.48986C7.06438 7.48986 6.21232 7.13692 5.58409 6.50869C4.95586 5.88046 4.60292 5.0284 4.60292 4.13995C4.60292 3.2515 4.95586 2.39944 5.58409 1.77121C6.21232 1.14298 7.06438 0.790039 7.95283 0.790039C8.84129 0.790039 9.69335 1.14298 10.3216 1.77121C10.9498 2.39944 11.3027 3.2515 11.3027 4.13995ZM1.25391 16.7517C1.28261 14.994 2.00101 13.318 3.25418 12.0851C4.50735 10.8522 6.19488 10.1613 7.95283 10.1613C9.71079 10.1613 11.3983 10.8522 12.6515 12.0851C13.9047 13.318 14.6231 14.994 14.6518 16.7517C12.5502 17.7154 10.2649 18.2127 7.95283 18.2096C5.56234 18.2096 3.29333 17.6879 1.25391 16.7517Z"
                            stroke="black" stroke-width="1.33996" stroke-linecap="round" stroke-linejoin="round" />
                        <path
                            d="M15.7444 11.1211V9.14064C15.7444 8.70292 15.5705 8.28314 15.261 7.97363C14.9515 7.66412 14.5317 7.49023 14.094 7.49023C13.6563 7.49023 13.2365 7.66412 12.927 7.97363C12.6175 8.28314 12.4436 8.70292 12.4436 9.14064V11.1211M17.4414 10.244L17.9973 15.5253C18.0281 15.8179 17.7992 16.0723 17.5048 16.0723H10.6832C10.6137 16.0724 10.545 16.0579 10.4816 16.0297C10.4181 16.0015 10.3613 15.9602 10.3148 15.9086C10.2683 15.857 10.2332 15.7963 10.2117 15.7302C10.1903 15.6642 10.1829 15.5943 10.1902 15.5253L10.7465 10.244C10.7594 10.1223 10.8168 10.0097 10.9077 9.92788C10.9987 9.84605 11.1167 9.80078 11.239 9.8008H16.949C17.2025 9.8008 17.415 9.99224 17.4414 10.244ZM12.6086 11.1211C12.6086 11.1649 12.5912 11.2069 12.5603 11.2378C12.5293 11.2688 12.4874 11.2862 12.4436 11.2862C12.3998 11.2862 12.3578 11.2688 12.3269 11.2378C12.2959 11.2069 12.2785 11.1649 12.2785 11.1211C12.2785 11.0773 12.2959 11.0354 12.3269 11.0044C12.3578 10.9735 12.3998 10.9561 12.4436 10.9561C12.4874 10.9561 12.5293 10.9735 12.5603 11.0044C12.5912 11.0354 12.6086 11.0773 12.6086 11.1211ZM15.9094 11.1211C15.9094 11.1649 15.892 11.2069 15.8611 11.2378C15.8301 11.2688 15.7882 11.2862 15.7444 11.2862C15.7006 11.2862 15.6586 11.2688 15.6277 11.2378C15.5967 11.2069 15.5793 11.1649 15.5793 11.1211C15.5793 11.0773 15.5967 11.0354 15.6277 11.0044C15.6586 10.9735 15.7006 10.9561 15.7444 10.9561C15.7882 10.9561 15.8301 10.9735 15.8611 11.0044C15.892 11.0354 15.9094 11.0773 15.9094 11.1211Z"
                            fill="white" />
                        <path
                            d="M15.7444 11.1211V9.14064C15.7444 8.70292 15.5705 8.28314 15.261 7.97363C14.9515 7.66412 14.5317 7.49023 14.094 7.49023C13.6563 7.49023 13.2365 7.66412 12.927 7.97363C12.6175 8.28314 12.4436 8.70292 12.4436 9.14064V11.1211M17.4414 10.244L17.9973 15.5253C18.0281 15.8179 17.7992 16.0723 17.5048 16.0723H10.6832C10.6137 16.0724 10.545 16.0579 10.4816 16.0297C10.4181 16.0015 10.3613 15.9602 10.3148 15.9086C10.2683 15.857 10.2332 15.7963 10.2117 15.7302C10.1903 15.6642 10.1829 15.5943 10.1902 15.5253L10.7465 10.244C10.7594 10.1223 10.8168 10.0097 10.9077 9.92788C10.9987 9.84605 11.1167 9.80078 11.239 9.8008H16.949C17.2025 9.8008 17.415 9.99224 17.4414 10.244ZM12.6086 11.1211C12.6086 11.1649 12.5912 11.2069 12.5603 11.2378C12.5293 11.2688 12.4874 11.2862 12.4436 11.2862C12.3998 11.2862 12.3578 11.2688 12.3269 11.2378C12.2959 11.2069 12.2785 11.1649 12.2785 11.1211C12.2785 11.0773 12.2959 11.0354 12.3269 11.0044C12.3578 10.9735 12.3998 10.9561 12.4436 10.9561C12.4874 10.9561 12.5293 10.9735 12.5603 11.0044C12.5912 11.0354 12.6086 11.0773 12.6086 11.1211ZM15.9094 11.1211C15.9094 11.1649 15.892 11.2069 15.8611 11.2378C15.8301 11.2688 15.7882 11.2862 15.7444 11.2862C15.7006 11.2862 15.6586 11.2688 15.6277 11.2378C15.5967 11.2069 15.5793 11.1649 15.5793 11.1211C15.5793 11.0773 15.5967 11.0354 15.6277 11.0044C15.6586 10.9735 15.7006 10.9561 15.7444 10.9561C15.7882 10.9561 15.8301 10.9735 15.8611 11.0044C15.892 11.0354 15.9094 11.0773 15.9094 11.1211Z"
                            stroke="black" stroke-width="1.00966" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>


                    <div>مشتریان</div>
                </a>
                <ul class="menu-sub">
                    @can('customers-add')
                        <li class="menu-item add-customer {{ Request::routeIs(['customers.create']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('customers.create') }}">
                                <div>ثبت مشتری</div>
                            </a>
                        </li>
                    @endcan

                    @can('customers')
                        <li class="menu-item search {{ Request::routeIs(['customers.search']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('customers.search') }}">
                                <div>جستجوی مشتری</div>
                            </a>
                        </li>
                        <li
                            class="menu-item list {{ Request::routeIs(['customers.index', 'customers.trashed.get']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('customers.index') }}">
                                <div>لیست مشتریان</div>
                            </a>
                        </li>
                        <li
                            class="menu-item activeCustomers {{ Request::routeIs(['customers.activeCustomers']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('customers.activeCustomers') }}">
                                <div>مشتریان فعال</div>
                            </a>
                        </li>
                        <li class="menu-item crm-dashboard {{ Request::routeIs(['crm.dashboard.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.dashboard.index') }}">
                                <div>داشبورد CRM</div>
                            </a>
                        </li>
                        <li class="menu-item crm-workbench {{ Request::routeIs(['crm.workbench.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.workbench.index') }}">
                                <div>کارتابل CRM و mentionها</div>
                            </a>
                        </li>
                        <li class="menu-item crm-followups {{ Request::routeIs(['crm.followups.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.followups.index') }}">
                                <div>CRM و پیگیری ها</div>
                            </a>
                        </li>
                        <li class="menu-item crm-leads {{ Request::routeIs(['crm.leads.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.leads.index') }}">
                                <div>سرنخ های CRM</div>
                            </a>
                        </li>
                        <li
                            class="menu-item crm-service-tickets {{ Request::routeIs(['crm.service-tickets.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.service-tickets.index') }}">
                                <div>خدمات پس از فروش</div>
                            </a>
                        </li>
                        <li class="menu-item crm-call-center {{ Request::routeIs(['crm.call-center.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.call-center.index') }}">
                                <div>مرکز تماس CRM</div>
                            </a>
                        </li>
                        <li
                            class="menu-item crm-campaigns {{ Request::routeIs(['crm.campaigns.*', 'crm.loyalty.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.campaigns.index') }}">
                                <div>کمپین و وفاداری CRM</div>
                            </a>
                        </li>
                        <li
                            class="menu-item crm-customer-portal {{ Request::routeIs(['crm.customer-portal.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.customer-portal.index') }}">
                                <div>پورتال مشتری و نماینده</div>
                            </a>
                        </li>
                        <li class="menu-item crm-public-api {{ Request::routeIs(['crm.public-api.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.public-api.index') }}">
                                <div>API عمومی CRM</div>
                            </a>
                        </li>
                        <li
                            class="menu-item crm-integrations {{ Request::routeIs(['crm.integrations.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.integrations.index') }}">
                                <div>Integrationهای CRM</div>
                            </a>
                        </li>
                        <li class="menu-item crm-health {{ Request::routeIs(['crm.health.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.health.index') }}">
                                <div>سلامت و Audit CRM</div>
                            </a>
                        </li>
                        <li
                            class="menu-item crm-employee-performance {{ Request::routeIs(['crm.employee-performance.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.employee-performance.index') }}">
                                <div>عملکرد و coaching کارمندان</div>
                            </a>
                        </li>
                        <li
                            class="menu-item crm-opportunities {{ Request::routeIs(['crm.opportunities.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.opportunities.index') }}">
                                <div>Pipeline فرصت فروش</div>
                            </a>
                        </li>
                        <li
                            class="menu-item crm-sales-boards {{ Request::routeIs(['crm.sales-boards.*']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('crm.sales-boards.index') }}">
                                <div>کاریز فروش کانبان</div>
                            </a>
                        </li>
                    @endcan
                    @can('customersCreatedByMe')
                        <li
                            class="menu-item customers_createdByMe  {{ Request::routeIs(['customers.createdByMe']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('customers.createdByMe') }}">
                                <div>مشتریان ثبت شده من</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @if ($featureWarehouseManagement)
            @can('stocks')
                <li class="menu-item stocks {{ Request::routeIs(['stocks.create', 'stocks.index', 'stocks.need_entity', 'stocks.entrance', 'stocks.entrance_transfer', 'stocks.import_stock', 'stocks.PrCartex', 'stocks.PrCartexList', 'stocks.store_cartex', 'stocks.inventoryBalances', 'stocks.inventoryMovements', 'stocks.inventoryReorder', 'stocks.inventoryAdjustments', 'stocks.inventoryAdjustments.create', 'stocks.ProductionByExtraction', 'stocks.productionFormulas.*', 'stocks.ProductionByFormulaProcess', 'stocks.storeProducts', 'stocks.StoreProductsCartex']) ? 'open' : '' }}"
                    data-menu-key="production">
                    <a class="menu-link menu-toggle" href="javascript:void(0);">
                        <svg width="18" height="20" viewBox="0 0 18 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M16.5 4.80769C16.5 6.91046 13.1418 8.61538 9 8.61538C4.85818 8.61538 1.5 6.91046 1.5 4.80769M16.5 4.80769C16.5 2.70492 13.1418 1 9 1C4.85818 1 1.5 2.70492 1.5 4.80769M16.5 4.80769V15.1923C16.5 17.2951 13.1418 19 9 19C4.85818 19 1.5 17.2951 1.5 15.1923V4.80769M16.5 4.80769V8.26923M1.5 4.80769V8.26923M16.5 8.26923V11.7308C16.5 13.8335 13.1418 15.5385 9 15.5385C4.85818 15.5385 1.5 13.8335 1.5 11.7308V8.26923M16.5 8.26923C16.5 10.372 13.1418 12.0769 9 12.0769C4.85818 12.0769 1.5 10.372 1.5 8.26923"
                                stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div>تولید</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item needs {{ Request::routeIs(['stocks.entrance']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('stocks.entrance') }}">
                                <div>درخواست تولید</div>
                            </a>
                        </li>
                        <li class="menu-item needs {{ Request::routeIs(['stocks.import_stock']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('stocks.import_stock') }}">
                                <div>ورودی انبار</div>
                            </a>
                        </li>
                        <li
                            class="menu-item export {{ Request::routeIs(['stocks.ProductionByExtraction', 'stocks.productionFormulas.*', 'stocks.ProductionByFormulaProcess']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('stocks.ProductionByExtraction') }}">
                                <div>تولید و فرمول ساخت</div>
                            </a>
                        </li>
                        @if ($featureMultiWarehouse)
                            <li
                                class="menu-item export {{ Request::routeIs(['stocks.entrance_transfer']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('stocks.entrance_transfer') }}">
                                    <div>انتقال بین انبار</div>
                                </a>
                            </li>
                        @endif
                        <li
                            class="menu-item cardex {{ Request::routeIs(['stocks.PrCartex', 'stocks.PrCartexList', 'stocks.store_cartex', 'stocks.inventoryBalances', 'stocks.inventoryMovements', 'stocks.inventoryAdjustments', 'stocks.inventoryAdjustments.create', 'stocks.storeProducts', 'stocks.StoreProductsCartex']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('stocks.store_cartex') }}">
                                <div>کاردکس محصولات</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan
        @endif

        @if ($featureWarehouseManagement)
            @canany(['store-delivery', 'stocks'])
                <li class="menu-item anbarotozi {{ Request::routeIs(['deliveries.Outgoing', 'deliveries.Outgoing_by_items', 'deliveries.compeleted', 'invoices.assigned_to_drivers', 'purchase-orders.index', 'purchase-orders.create', 'purchase-orders.directSupply', 'purchase-orders.approvals', 'purchase-orders.report', 'purchase-service-invoices.index', 'receipt.index', 'stocks.storeReceipts', 'stocks.storeReceiptShow', 'stocks.inventoryBalances', 'stocks.inventoryMovements', 'stocks.inventoryReorder', 'stocks.inventoryAdjustments', 'stocks.inventoryAdjustments.create', 'stocks.PrCartex', 'stocks.PrCartexList', 'stocks.store_cartex']) ? 'open' : '' }}"
                    data-menu-key="warehouse_supply">
                    <a class="menu-link menu-toggle" href="javascript:void(0);">
                        <svg width="22" height="20" viewBox="0 0 22 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12.2477 19V11.5C12.2477 11.3011 12.3267 11.1103 12.4673 10.9697C12.608 10.829 12.7988 10.75 12.9977 10.75H15.9977C16.1966 10.75 16.3873 10.829 16.528 10.9697C16.6687 11.1103 16.7477 11.3011 16.7477 11.5V19M12.2477 19H1.10767M12.2477 19H16.7477M16.7477 19H20.3877M18.9977 19V7.349M18.9977 7.349C18.3978 7.6951 17.6966 7.82304 17.0131 7.71113C16.3296 7.59922 15.7059 7.25435 15.2477 6.735C14.6977 7.357 13.8937 7.75 12.9977 7.75C12.5719 7.75041 12.151 7.66 11.763 7.48479C11.375 7.30959 11.0289 7.05363 10.7477 6.734C10.1977 7.357 9.39367 7.75 8.49767 7.75C8.07195 7.75041 7.65104 7.66 7.26304 7.48479C6.87504 7.30959 6.52888 7.05363 6.24767 6.734C5.78959 7.25351 5.16591 7.59858 4.48242 7.71067C3.79892 7.82276 3.09769 7.69498 2.49767 7.349M18.9977 7.349C19.3963 7.11891 19.7366 6.80019 19.9923 6.41751C20.248 6.03484 20.4122 5.59846 20.4722 5.14215C20.5323 4.68584 20.4865 4.22184 20.3385 3.78605C20.1905 3.35026 19.9442 2.95436 19.6187 2.629L18.4287 1.44C18.1476 1.15862 17.7664 1.00035 17.3687 1H4.12567C3.72812 1.00008 3.34687 1.15798 3.06567 1.439L1.87667 2.629C1.55191 2.95474 1.30629 3.3507 1.15874 3.78637C1.01119 4.22204 0.965667 4.68577 1.02567 5.14182C1.08567 5.59786 1.24959 6.03403 1.50481 6.41671C1.76002 6.79939 2.09971 7.11835 2.49767 7.349M2.49767 19V7.349M5.49767 16H9.24767C9.44658 16 9.63735 15.921 9.778 15.7803C9.91865 15.6397 9.99767 15.4489 9.99767 15.25V11.5C9.99767 11.3011 9.91865 11.1103 9.778 10.9697C9.63735 10.829 9.44658 10.75 9.24767 10.75H5.49767C5.29876 10.75 5.10799 10.829 4.96734 10.9697C4.82669 11.1103 4.74767 11.3011 4.74767 11.5V15.25C4.74767 15.664 5.08367 16 5.49767 16Z"
                                stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                        <div>انبار و تامین</div>
                    </a>
                    <ul class="menu-sub">
                        <li
                            class="menu-item purchase_requisitions {{ Request::routeIs(['purchase-requisitions.index', 'purchase-requisitions.create', 'purchase-requisitions.show']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('purchase-requisitions.index') }}">
                                <div>استعلام بها</div>
                            </a>
                        </li>
                        <li
                            class="menu-item purchase_orders {{ Request::routeIs(['purchase-orders.index', 'purchase-orders.create']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('purchase-orders.index') }}">
                                <div>سفارش خرید</div>
                            </a>
                        </li>
                        <li
                            class="menu-item purchase_direct_supply {{ Request::routeIs(['purchase-orders.directSupply']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('purchase-orders.directSupply') }}">
                                <div>تامین مستقیم</div>
                            </a>
                        </li>
                        <li
                            class="menu-item purchase_approvals {{ Request::routeIs(['purchase-orders.approvals']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('purchase-orders.approvals') }}">
                                <div>تایید و بودجه خرید</div>
                            </a>
                        </li>
                        <li
                            class="menu-item purchase_order_report {{ Request::routeIs(['purchase-orders.report']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('purchase-orders.report') }}">
                                <div>گزارش خرید</div>
                            </a>
                        </li>
                        <li
                            class="menu-item purchase_supplier_ledger {{ Request::routeIs(['purchase-orders.supplierLedger']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('purchase-orders.supplierLedger') }}">
                                <div>گردش تامین کننده</div>
                            </a>
                        </li>
                        <li
                            class="menu-item purchase_commitment_report {{ Request::routeIs(['purchase-orders.commitmentReport']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('purchase-orders.commitmentReport') }}">
                                <div>تعهد و دریافت خرید</div>
                            </a>
                        </li>
                        <li
                            class="menu-item purchase_price_report {{ Request::routeIs(['purchase-orders.priceReport']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('purchase-orders.priceReport') }}">
                                <div>کنترل قیمت خرید</div>
                            </a>
                        </li>
                        <li
                            class="menu-item purchase_service_invoices {{ Request::routeIs(['purchase-service-invoices.index']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('purchase-service-invoices.index') }}">
                                <div>فاکتور خدمات خرید</div>
                            </a>
                        </li>
                        <li
                            class="menu-item receipts {{ Request::routeIs(['receipt.index', 'stocks.storeReceipts', 'stocks.storeProductCardex', 'stocks.storeReceiptShow']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('receipt.index') }}">
                                <div>رسید انبار</div>
                            </a>
                        </li>
                        <li
                            class="menu-item inventory_balances {{ Request::routeIs(['stocks.inventoryBalances']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('stocks.inventoryBalances') }}">
                                <div>موجودی لحظه ای</div>
                            </a>
                        </li>
                        <li
                            class="menu-item inventory_movements {{ Request::routeIs(['stocks.inventoryMovements', 'stocks.PrCartex', 'stocks.PrCartexList', 'stocks.store_cartex']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('stocks.inventoryMovements') }}">
                                <div>دفتر گردش و کاردکس</div>
                            </a>
                        </li>
                        <li
                            class="menu-item inventory_traceability {{ Request::routeIs(['stocks.inventoryTraceability']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('stocks.inventoryTraceability') }}">
                                <div>ردیابی batch و سریال</div>
                            </a>
                        </li>
                        <li
                            class="menu-item inventory_reservations {{ Request::routeIs(['stocks.inventoryReservations']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('stocks.inventoryReservations') }}">
                                <div>رزرو موجودی</div>
                            </a>
                        </li>
                        <li
                            class="menu-item inventory_valuation {{ Request::routeIs(['stocks.inventoryValuation']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('stocks.inventoryValuation') }}">
                                <div>گزارش ریالی انبار</div>
                            </a>
                        </li>
                        <li
                            class="menu-item inventory_reorder {{ Request::routeIs(['stocks.inventoryReorder']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('stocks.inventoryReorder') }}">
                                <div>کمبود و پیشنهاد سفارش</div>
                            </a>
                        </li>
                        <li
                            class="menu-item inventory_slow_moving {{ Request::routeIs(['stocks.inventorySlowMoving']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('stocks.inventorySlowMoving') }}">
                                <div>کالاهای کم فروش و راکد</div>
                            </a>
                        </li>
                        <li
                            class="menu-item inventory_adjustments {{ Request::routeIs(['stocks.inventoryAdjustments', 'stocks.inventoryAdjustments.create']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('stocks.inventoryAdjustments') }}">
                                <div>انبارگردانی و اصلاحیه</div>
                            </a>
                        </li>
                        @if ($featureMultiWarehouse)
                            <li
                                class="menu-item warehouse_transfer {{ Request::routeIs(['stocks.entrance_transfer']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('stocks.entrance_transfer') }}">
                                    <div>انتقال بین انبار</div>
                                </a>
                            </li>
                        @endif
                        @if ($featureDistribution)
                            <li
                                class="menu-item preOrderOutput {{ Request::routeIs(['deliveries.preOrderOutput']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('deliveries.preOrderOutput') }}">
                                    <div>حواله خروج پیش سفارش</div>
                                </a>
                            </li>
                            <li class="menu-item havale {{ Request::routeIs(['deliveries.Outgoing']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('deliveries.Outgoing') }}">
                                    <div>خروج انبار</div>
                                </a>
                            </li>
                            <li
                                class="menu-item dayOrders {{ Request::routeIs(['deliveries.dayOrders']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('deliveries.dayOrders') }}">
                                    <div>سرجمع سفارشات روزانه</div>
                                </a>
                            </li>
                            <li
                                class="menu-item Outgoing_by_items {{ Request::routeIs(['deliveries.Outgoing_by_items']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('deliveries.Outgoing_by_items') }}">
                                    <div>آماده ارسال</div>
                                </a>
                            </li>
                            <li
                                class="menu-item delivery_compeleted {{ Request::routeIs(['deliveries.compeleted']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('deliveries.compeleted') }}">گزارش خروج</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endcanany
        @endif

        @if ($IsVisitor == 1 && $featureRouteManagement)
            @can('tasks-list')
                <li class="menu-item tasks {{ Request::routeIs(['tasks.MyTasks']) ? 'open' : '' }}"
                    data-menu-key="visitor_tasks">
                    <a class="menu-link" href="{{ route('tasks.MyTasks') }}">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.33333 4.21485V11.5482M11.6667 6.21485V13.5482M12.1138 16.6575L16.4471 14.4913C16.7858 14.3224 17 13.9757 17 13.5971V2.4993C17 1.75619 16.2178 1.27263 15.5529 1.60508L12.1138 3.32419C11.832 3.46552 11.5004 3.46552 11.2196 3.32419L6.78044 1.10552C6.64162 1.03613 6.48854 1 6.33333 1C6.17813 1 6.02505 1.03613 5.88622 1.10552L1.55289 3.27174C1.21333 3.44152 1 3.78819 1 4.16597V15.2637C1 16.0069 1.78222 16.4904 2.44711 16.158L5.88622 14.4389C6.168 14.2975 6.49956 14.2975 6.78044 14.4389L11.2196 16.6584C11.5013 16.7989 11.8329 16.7989 12.1138 16.6584V16.6575Z"
                                stroke="black" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div>مسیرهای من</div>
                    </a>
                </li>
            @endcan
        @endif

        @if ($IsVisitor == 1 || $IsAgent == 1)
            <li class="menu-item orders" data-menu-key="visitor_orders">
                <a class="menu-link menu-toggle" href="javascript:void(0);">
                    <svg width="19" height="18" viewBox="0 0 19 18" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M1 1H2.21504C2.66213 1 3.05224 1.30069 3.16796 1.732L3.50371 2.99175M3.50371 2.99175C8.38722 2.85489 13.266 3.39777 18 4.60479C17.2776 6.75608 16.4194 8.84602 15.4367 10.8623H5.60241M3.50371 2.99175L5.60241 10.8623M5.60241 10.8623C4.90491 10.8623 4.23597 11.1394 3.74276 11.6326C3.24955 12.1258 2.97246 12.7948 2.97246 13.4923H16.7797M4.28744 16.1222C4.28744 16.2966 4.21817 16.4638 4.09486 16.5871C3.97156 16.7104 3.80433 16.7797 3.62995 16.7797C3.45557 16.7797 3.28834 16.7104 3.16504 16.5871C3.04173 16.4638 2.97246 16.2966 2.97246 16.1222C2.97246 15.9478 3.04173 15.7806 3.16504 15.6573C3.28834 15.534 3.45557 15.4647 3.62995 15.4647C3.80433 15.4647 3.97156 15.534 4.09486 15.6573C4.21817 15.7806 4.28744 15.9478 4.28744 16.1222ZM15.4647 16.1222C15.4647 16.2966 15.3955 16.4638 15.2722 16.5871C15.1489 16.7104 14.9816 16.7797 14.8072 16.7797C14.6329 16.7797 14.4656 16.7104 14.3423 16.5871C14.219 16.4638 14.1498 16.2966 14.1498 16.1222C14.1498 15.9478 14.219 15.7806 14.3423 15.6573C14.4656 15.534 14.6329 15.4647 14.8072 15.4647C14.9816 15.4647 15.1489 15.534 15.2722 15.6573C15.3955 15.7806 15.4647 15.9478 15.4647 16.1222Z"
                            stroke="black" stroke-width="1.40444" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    <div>سفارش ها</div>
                </a>
                <ul class="menu-sub">
                    @can(['neworder'])
                        <li class="menu-item add-order">
                            <a class="menu-link" href="{{ route('products.neworder') }}">
                                <div>ثبت سفارش</div>
                            </a>
                        </li>
                    @endcan
                    @if ($featureManagerOrderApproval)
                        <li class="menu-item waiting">
                            <a class="menu-link" href="{{ route('waiting_orders') }}">
                                <div>سفارشات در انتظار سرپرست</div>
                            </a>
                        </li>
                    @endif
                    <li class="menu-item myhistory">
                        <a class="menu-link" href="{{ route('history_orders') }}">
                            <div>تاریخچه سفارشات من</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['invoices.myInvoices']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('invoices.myInvoices') }}">
                            <div>مرور فاکتورهای من</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        @if ($IsVisitor == 0)
            @can('invoices')
                <li class="menu-item factors {{ Request::routeIs(['invoices.index', 'invoices.active_list', 'invoices.denciled', 'invoices.compeleted', 'invoices.edit', 'invoices.all_invoices', 'invoices.trashed', 'invoices.reporter', 'invoices.create', 'details.list', 'pishFactorInfo', 'invoices.myInvoices', 'ecommerce.index']) ? 'open' : '' }}"
                    data-menu-key="sales">
                    <a class="menu-link menu-toggle" href="javascript:void(0);">
                        <svg width="16" height="20" viewBox="0 0 16 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M15 12.0769V9.65385C15 8.82759 14.6681 8.03518 14.0774 7.45094C13.4866 6.86669 12.6854 6.53846 11.85 6.53846H10.45C10.1715 6.53846 9.90445 6.42905 9.70754 6.2343C9.51062 6.03955 9.4 5.77542 9.4 5.5V4.11538C9.4 3.28913 9.06813 2.49672 8.47739 1.91248C7.88665 1.32823 7.08543 1 6.25 1H4.5M4.5 12.7692H11.5M4.5 15.5385H8M6.6 1H2.05C1.4704 1 1 1.46523 1 2.03846V17.9615C1 18.5348 1.4704 19 2.05 19H13.95C14.5296 19 15 18.5348 15 17.9615V9.30769C15 7.10436 14.115 4.99126 12.5397 3.43327C10.9644 1.87527 8.82782 1 6.6 1Z"
                                stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <?php
                        if (!$featureManagerOrderApproval) {
                            $WaitingOrders = 0;
                        } elseif ($IsLeader == 1) {
                            $WaitingOrders = App\Models\Pishfactor::forOrganizations(auth()->user())
                                ->where('sarparast_id', auth()->user()->id)
                                ->whereIn('status', [0, 5])
                                ->count();
                        } else {
                            $WaitingOrders = App\Models\Pishfactor::forOrganizations(auth()->user())
                                ->whereIn('status', [0, 5])
                                ->count();
                        }
                        
                        ?>
                        <div>فروش</div>
                        @if ($WaitingOrders > 0)
                            <div class="badge bg-danger rounded-pill ms-auto">{{ $WaitingOrders }}</div>
                        @endif
                    </a>
                    <ul class="menu-sub">
                        @canany(['neworder', 'invoice-add'])
                            <li class="menu-item add_new_factor">
                                <a class="menu-link" href="{{ route('products.neworder') }}">
                                    <di>ثبت پیش فاکتور جدید</di>
                                </a>
                            </li>
                        @endcan
                        @if ($featureManagerOrderApproval)
                            <li class="menu-item waiting {{ Request::routeIs(['invoices.index']) ? 'active' : '' }}">

                                <a class="menu-link" href="{{ route('invoices.index') }}">
                                    <di>سفارشات در انتظار</di>
                                    @if ($WaitingOrders > 0)
                                        <div class="badge bg-danger rounded-pill ms-auto">{{ $WaitingOrders }}</div>
                                    @endif
                                </a>
                            </li>
                        @endif
                        <li class="menu-item accepted {{ Request::routeIs(['invoices.active_list']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('invoices.active_list') }}">
                                <div>فاکتورهای تایید شده</div>
                            </a>
                        </li>

                        @if (auth()->user()->isAdmin == 1)
                            <li class="menu-item denciled {{ Request::routeIs(['invoices.denciled']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('invoices.denciled') }}">
                                    <div>فاکتورهای رد شده</div>
                                </a>
                            </li>
                            <li
                                class="menu-item compeleted {{ Request::routeIs(['invoices.compeleted']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('invoices.compeleted') }}">
                                    <div>فاکتورهای تکمیل شده</div>
                                </a>
                            </li>
                            <li
                                class="menu-item all_invoices {{ Request::routeIs(['invoices.all_invoices']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('invoices.all_invoices') }}?neworder=ok">
                                    <di>همه فاکتور ها</di>
                                </a>
                            </li>
                        @endif
                        <?php /*
                        @can('Accounting')
                            <li class="menu-item {{ Request::routeIs(['Accounting.payed']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('Accounting.payed') }}">تسویه شده</a>
                            </li>
                            <li class="menu-item {{ Request::routeIs(['Accounting.unpayed']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('Accounting.unpayed') }}">تسویه نشده</a>
                            </li>
                        @endcan
                                         <li class="menu-item {{ Request::routeIs(['invoices.all_invoices']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('invoices.all_invoices') }}">همه فاکتورها</a>
                            </li>   */
                        ?>
                        @if (auth()->user()->isAdmin == 1)

                            <li class="menu-item {{ Request::routeIs(['invoices.reporter']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('invoices.reporter') }}">گزارش گیری</a>
                            </li>
                            <li class="menu-item {{ Request::routeIs(['ecommerce.index']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('ecommerce.index') }}">فروشگاه اینترنتی</a>
                            </li>
                        @endif
                        @if ($IsLeader == 1)
                            <li class="menu-item {{ Request::routeIs(['invoices.myInvoices']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('invoices.myInvoices') }}">مرور فاکتورهای من</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endcan
        @endif

        @if ($featureDistribution)
            @can('drivers')
                <li class="menu-item {{ Request::routeIs(['deliveries.shipments', 'deliveries.addShipment', 'deliveries.storeShipments', 'deliveries.EditShipment']) ? 'open' : '' }}"
                    data-menu-key="shipping_distribution">
                    <a class="menu-link menu-toggle" href="javascript:void(0)">
                        <svg width="21" height="17" viewBox="0 0 21 17" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M7.04783 14.1C7.04783 14.4713 6.90171 14.8274 6.6416 15.09C6.3815 15.3525 6.02872 15.5 5.66088 15.5C5.29304 15.5 4.94026 15.3525 4.68015 15.09C4.42005 14.8274 4.27392 14.4713 4.27392 14.1M7.04783 14.1C7.04783 13.7288 6.90171 13.3727 6.6416 13.1101C6.3815 12.8476 6.02872 12.7001 5.66088 12.7001C5.29304 12.7001 4.94026 12.8476 4.68015 13.1101C4.42005 13.3727 4.27392 13.7288 4.27392 14.1M7.04783 14.1H12.5956M4.27392 14.1H2.54023C2.26435 14.1 1.99977 13.9894 1.80469 13.7925C1.60961 13.5956 1.50002 13.3285 1.50002 13.0501V9.90018M12.5956 14.1H14.6761M12.5956 14.1V9.90018M1.50002 9.90018V2.77441C1.49855 2.51884 1.59106 2.27181 1.75964 2.08113C1.92823 1.89044 2.1609 1.76967 2.41263 1.74217C5.49449 1.41928 8.60118 1.41928 11.683 1.74217C12.2055 1.7963 12.5956 2.24429 12.5956 2.77441V3.66851M1.50002 9.90018H12.5956M17.45 14.1C17.45 14.4713 17.3039 14.8274 17.0438 15.09C16.7837 15.3525 16.4309 15.5 16.063 15.5C15.6952 15.5 15.3424 15.3525 15.0823 15.09C14.8222 14.8274 14.6761 14.4713 14.6761 14.1M17.45 14.1C17.45 13.7288 17.3039 13.3727 17.0438 13.1101C16.7837 12.8476 16.4309 12.7001 16.063 12.7001C15.6952 12.7001 15.3424 12.8476 15.0823 13.1101C14.8222 13.3727 14.6761 13.7288 14.6761 14.1M17.45 14.1H18.4902C19.0644 14.1 19.5341 13.6297 19.4981 13.051C19.3123 9.96891 18.2839 6.99906 16.5272 4.47115C16.3599 4.23435 16.1414 4.03905 15.8883 3.9C15.6352 3.76095 15.3541 3.68179 15.0663 3.66851H12.5956M12.5956 3.66851V9.90018"
                                stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div>باربری و توزیع</div>
                    </a>
                    <ul class="menu-sub">
                        @can('add-shipment')
                            <li
                                class="menu-item shipments {{ Request::routeIs(['deliveries.addShipment', 'deliveries.storeShipments']) ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('deliveries.addShipment') }}">
                                    <div>تعریف بار/مرسولات</div>
                                </a>
                            </li>
                        @endcan
                        <li
                            class="menu-item assigned_to_drivers {{ Request::routeIs(['deliveries.shipments', 'deliveries.EditShipment']) ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('deliveries.shipments') }}">
                                <div>مرور توزیع</div>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link" href="ui-alerts.html">
                                <div>مرور صندوق</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan
        @endif

        @can('Accounting')
            <li class="menu-item accounting {{ Request::routeIs(['Accounting.index', 'Accounting.AccountingReviews', 'Accounting.vouchers', 'Accounting.vouchers.create', 'Accounting.vouchers.edit', 'Accounting.voucherTemplates', 'Accounting.legalLedgers', 'Accounting.detailedLedgers', 'Accounting.analyticDimensions', 'Accounting.currencyBalances', 'Accounting.financialStatements', 'Accounting.revenueCenters', 'Accounting.incomes', 'Accounting.fiscalClosing', 'Accounting.expenses', 'Accounting.payroll', 'contracting.projects', 'taxpayer.index', 'Accounting.treasury', 'Accounting.treasury.create', 'Accounting.treasury.transfer.create', 'Accounting.treasury.cheques', 'Accounting.treasury.cheques.aging', 'Accounting.treasury.chequeBooks', 'Accounting.treasury.bankReconciliation', 'Accounting.treasury.liquidity', 'Accounting.treasury.cashForecast', 'Accounting.treasury.pettyCash', 'Accounting.payed', 'Accounting.unpayed', 'Accounting.cashpay', 'Accounting.checkpay', 'Accounting.unknown']) ? 'open' : '' }}"
                data-menu-key="accounting">
                <a class="menu-link menu-toggle" href="javascript:void(0)">
                    <svg width="21" height="17" viewBox="0 0 21 17" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M1.5 14.0397C6.42518 14.0358 11.3289 14.6823 16.0818 15.9623C16.7529 16.1435 17.4231 15.6493 17.4231 14.9594V14.0397M2.88462 1V1.6863C2.88462 1.86832 2.81168 2.04288 2.68184 2.17159C2.55201 2.3003 2.37592 2.3726 2.19231 2.3726H1.5M1.5 2.3726V2.02945C1.5 1.46119 1.96523 1 2.53846 1H18.1154M1.5 2.3726V10.6082M18.1154 1V1.6863C18.1154 2.06514 18.4255 2.3726 18.8077 2.3726H19.5M18.1154 1H18.4615C19.0348 1 19.5 1.46119 19.5 2.02945V10.9514C19.5 11.5196 19.0348 11.9808 18.4615 11.9808H18.1154M1.5 10.6082V10.9514C1.5 11.2244 1.60941 11.4862 1.80416 11.6793C1.99891 11.8724 2.26304 11.9808 2.53846 11.9808H2.88462M1.5 10.6082H2.19231C2.37592 10.6082 2.55201 10.6805 2.68184 10.8092C2.81168 10.9379 2.88462 11.1125 2.88462 11.2945V11.9808M18.1154 11.9808V11.2945C18.1154 11.1125 18.1883 10.9379 18.3182 10.8092C18.448 10.6805 18.6241 10.6082 18.8077 10.6082H19.5M18.1154 11.9808H2.88462M13.2692 6.49041C13.2692 7.21849 12.9775 7.91674 12.4581 8.43157C11.9388 8.9464 11.2344 9.23562 10.5 9.23562C9.76555 9.23562 9.06119 8.9464 8.54186 8.43157C8.02253 7.91674 7.73077 7.21849 7.73077 6.49041C7.73077 5.76234 8.02253 5.06409 8.54186 4.54926C9.06119 4.03443 9.76555 3.74521 10.5 3.74521C11.2344 3.74521 11.9388 4.03443 12.4581 4.54926C12.9775 5.06409 13.2692 5.76234 13.2692 6.49041ZM16.0385 6.49041H16.0458V6.49774H16.0385V6.49041ZM4.96154 6.49041H4.96892V6.49774H4.96154V6.49041Z"
                            stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    <div>مالی و حسابداری</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Request::routeIs(['Accounting.index']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.index') }}">صندوق</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.AccountingReviews']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.AccountingReviews') }}">مرور حساب ها</a>
                    </li>
                    <li
                        class="menu-item {{ Request::routeIs(['Accounting.vouchers', 'Accounting.vouchers.create', 'Accounting.vouchers.edit']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.vouchers') }}">اسناد حسابداری</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.voucherTemplates']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.voucherTemplates') }}">الگوهای سند</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.legalLedgers']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.legalLedgers') }}">دفاتر و تراز آزمایشی</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.detailedLedgers']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.detailedLedgers') }}">دفتر کل و تراز
                            چندستونی</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.analyticDimensions']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.analyticDimensions') }}">گزارش تفصیل شناور</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.currencyBalances']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.currencyBalances') }}">مانده ارزی و تسعیر</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.financialStatements']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.financialStatements') }}">صورت های مالی</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.revenueCenters']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.revenueCenters') }}">مراکز درآمد</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.incomes']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.incomes') }}">درآمدها</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.fiscalClosing']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.fiscalClosing') }}">بستن دوره مالی</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.payroll']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.payroll') }}">حقوق و دستمزد</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['contracting.projects']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('contracting.projects') }}">پیمانکاری و پروژه</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['taxpayer.index']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('taxpayer.index') }}">سامانه مودیان</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.expenses']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.expenses') }}">هزینه ها و مراکز هزینه</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.companyAssets']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.companyAssets') }}">اموال شرکت</a>
                    </li>
                    <li
                        class="menu-item {{ Request::routeIs(['Accounting.treasury', 'Accounting.treasury.create']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.treasury') }}">دریافت و پرداخت</a>
                    </li>
                    <li
                        class="menu-item {{ Request::routeIs(['Accounting.treasury.transfer.create']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.treasury.transfer.create') }}">انتقال بین حساب
                            ها</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.treasury.cheques']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.treasury.cheques') }}">گزارش چک ها</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.treasury.cheques.aging']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.treasury.cheques.aging') }}">راس گیری چک</a>
                    </li>
                    <li
                        class="menu-item {{ Request::routeIs(['Accounting.treasury.bankReconciliation']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.treasury.bankReconciliation') }}">مغایرت
                            بانکی</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.treasury.liquidity']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.treasury.liquidity') }}">مانده نقدینگی</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.treasury.cashForecast']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.treasury.cashForecast') }}">پیش بینی نقدینگی</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.treasury.pettyCash']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.treasury.pettyCash') }}">تنخواه</a>
                    </li>
                    <li class="menu-item {{ Request::routeIs(['Accounting.treasury.chequeBooks']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.treasury.chequeBooks') }}">دسته چک و هشدارها</a>
                    </li>
                    <li
                        class="menu-item ProductsSales {{ Request::routeIs(['Accounting.ProductsSales']) ? 'active' : '' }}">
                        <a class="menu-link" href="{{ route('Accounting.ProductsSales') }}">مرور فروش</a>
                    </li>
                </ul>
            </li>
            @endif

            @can('formulation')
                <li class="menu-item " data-menu-key="formulation">
                    <a class="menu-link menu-toggle" href="javascript:void(0);">
                        <svg width="18" height="22" viewBox="0 0 18 22" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M16.5 13.6392V11.0142C16.5 9.1502 14.989 7.63916 13.125 7.63916H11.625C11.0037 7.63916 10.5 7.13548 10.5 6.51416V5.01416C10.5 3.1502 8.98896 1.63916 7.125 1.63916H5.25M5.25 9.88916L5.46967 9.66949C5.94214 9.19702 6.75 9.53164 6.75 10.1998V16.6401C6.75 17.1119 6.96398 17.5741 7.38992 17.7769C7.87777 18.0092 8.4237 18.1392 9 18.1392C10.4917 18.1392 11.7799 17.2682 12.384 16.0071C12.5888 15.5795 12.2316 15.1392 11.7574 15.1392H11.25M5.25 12.8892H12.75M7.5 1.63916H2.625C2.00368 1.63916 1.5 2.14284 1.5 2.76416V20.0142C1.5 20.6355 2.00368 21.1392 2.625 21.1392H15.375C15.9963 21.1392 16.5 20.6355 16.5 20.0142V10.6392C16.5 5.6686 12.4706 1.63916 7.5 1.63916Z"
                                stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>


                        <div>فرمول ها</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item">
                            <a class="menu-link" href="products_formulas.php">
                                <div>فرمول تولید محصولات</div>
                            </a>
                        </li>
                        <li class="menu-item products">
                            <a class="menu-link" href="price_formulas.php">
                                <div>فرمول قیمت محصولات</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan


        </ul>
    </aside>
    <!-- / Menu -->
