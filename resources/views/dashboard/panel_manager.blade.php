<?php use Hekmatinasser\Verta\Verta; ?>
<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
    data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer"
    data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport" />
    <title>داشبورد مدیر پنل — دکان ERP</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/apex-charts/apex-charts.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        :root {
            --pm-primary:   #7367f0;
            --pm-success:   #28c76f;
            --pm-warning:   #ff9f43;
            --pm-danger:    #ea5455;
            --pm-info:      #00cfe8;
            --pm-secondary: #82868b;
        }
        .stat-icon {
            width: 42px; height: 42px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px; font-size: 1.25rem; flex-shrink: 0;
        }
        .stat-card { border: none; box-shadow: 0 2px 12px rgba(115,103,240,.08); transition: box-shadow .2s; }
        .stat-card:hover { box-shadow: 0 4px 20px rgba(115,103,240,.18); }
        .pm-section-title {
            font-size: .78rem; text-transform: uppercase; letter-spacing: .06em;
            color: var(--pm-secondary); font-weight: 600; margin-bottom: .75rem;
        }
        .tab-sales-btn { cursor: pointer; }
        .tab-sales-btn.active { background: var(--pm-primary) !important; color: #fff !important; }
        .team-role-badge { font-size: .72rem; }
        .team-indent-1 { padding-right: 1rem; border-right: 3px solid #e8e5ff; }
        .team-indent-2 { padding-right: 2rem; border-right: 3px solid #d4f5e9; }
        .target-bar { height: 8px; border-radius: 4px; background: #e9ecef; }
        .target-bar-fill { height: 100%; border-radius: 4px; transition: width .6s; }
        .product-bar { height: 10px; border-radius: 5px; background: #f0f0f0; }
        .product-bar-fill { height: 100%; border-radius: 5px; }
        .trend-up   { color: #28c76f; }
        .trend-down { color: #ea5455; }
        .crm-mini-stat { border-radius: 10px; padding: .75rem 1rem; }
        @media (max-width: 575px) { .welcome-svg-wrap { display: none; } }
        .welcome-svg-wrap { position:absolute; bottom:0; left:1.5rem; width:160px; }
    </style>
</head>

@php
/* ═══════════════════════════════════════════════════════════
   Panel Manager Enhanced Dashboard — All queries inline
   Nuclear-safe: every section wrapped in try/catch.
   Never 500s.  Uses ONLY confirmed-safe columns.
═══════════════════════════════════════════════════════════ */
try {

    /* ── 1. Org ID resolution ── */
    $rawOrgId = auth()->user()->organization_id ?? 0;
    if (is_string($rawOrgId)) {
        $dec = json_decode($rawOrgId, true);
        $rawOrgId = (is_array($dec) && !empty($dec[0])) ? (int)$dec[0] : (int)$rawOrgId;
    }
    $orgId = (int)$rawOrgId;

    /* ── Helpers ── */
    $nf  = fn($n) => number_format((int)$n);
    $nfT = fn($n) => number_format((int)($n / 10));          // ریال → تومان
    $pct = fn($a,$b) => $b > 0 ? round(($a-$b)/$b*100,1) : ($a>0?100:0);

    try { $updatedAt = Verta::now()->format('H:i'); }
    catch (\Throwable $_) { $updatedAt = now()->format('H:i'); }

    /* ── Org name ── */
    $orgName = \DB::table('organizations')->where('id', $orgId)->value('title') ?? 'دارمینو';

    /* ── Date helpers ── */
    $today          = now()->startOfDay();
    $weekStart      = now()->startOfWeek();
    $monthStart     = now()->startOfMonth();
    $monthEnd       = now()->endOfMonth();
    $prevMonthStart = now()->subMonth()->startOfMonth();
    $prevMonthEnd   = now()->subMonth()->endOfMonth();

    /* ══════════════════════════════
       STAT CARDS
    ══════════════════════════════ */

    $customerCount = \DB::table('customers')
        ->where('organization_id', $orgId)->whereNull('deleted_at')->count();

    $invoiceCount = \DB::table('pishfactors')
        ->where('organization_id', $orgId)
        ->where('created_at', '>=', $monthStart)->where('created_at', '<=', $monthEnd)
        ->whereNull('deleted_at')->count();

    $revenue = (float)\DB::table('pishfactors')
        ->where('organization_id', $orgId)
        ->where('created_at', '>=', $monthStart)->where('created_at', '<=', $monthEnd)
        ->whereNull('deleted_at')
        ->sum(\DB::raw("REPLACE(fullPrice, ',', '')"));

    /* پرسنل — users with sales roles */
    try {
        $personnelCount = \DB::table('users as u')
            ->join('model_has_roles as mhr', fn($j) =>
                $j->on('mhr.model_id','=','u.id')->where('mhr.model_type','App\Models\User'))
            ->join('roles as r','r.id','=','mhr.role_id')
            ->whereIn('r.title',['leader','expert','visitor','driver','sales_manager','accountant'])
            ->whereRaw("u.organization_id LIKE ?", ["%{$orgId}%"])
            ->whereNull('u.deleted_at')->where('u.isActive',1)
            ->distinct()->count('u.id');
    } catch (\Throwable $_) {
        $personnelCount = \DB::table('users')
            ->whereRaw("organization_id LIKE ?", ["%{$orgId}%"])
            ->whereNull('deleted_at')->count();
    }

    /* محصولات فعال */
    try {
        $activeProductCount = \DB::table('products')
            ->whereRaw("JSON_CONTAINS(organization_id, ?, '$')", [json_encode($orgId)])
            ->where('isActive',1)->whereNull('deleted_at')->count();
    } catch (\Throwable $_) {
        try {
            $activeProductCount = \DB::table('products')
                ->whereRaw("organization_id LIKE ?", ["%{$orgId}%"])
                ->where('isActive',1)->whereNull('deleted_at')->count();
        } catch (\Throwable $_) { $activeProductCount = 0; }
    }

    /* موجودی انبار */
    try {
        $stockTotal = (int)\DB::table('stocks')
            ->where('organization_id',$orgId)->where('isActive',1)->sum('entity');
        $hasStocks = true;
    } catch (\Throwable $_) { $stockTotal = 0; $hasStocks = false; }

    /* ══════════════════════════════
       SALES TODAY / WEEK / MONTH
    ══════════════════════════════ */

    $salesTodayCount  = \DB::table('pishfactors')->where('organization_id',$orgId)
        ->where('created_at','>=',$today)->whereNull('deleted_at')->count();
    $salesTodayAmount = (float)\DB::table('pishfactors')->where('organization_id',$orgId)
        ->where('created_at','>=',$today)->whereNull('deleted_at')
        ->sum(\DB::raw("REPLACE(fullPrice, ',', '')"));

    $salesWeekCount  = \DB::table('pishfactors')->where('organization_id',$orgId)
        ->where('created_at','>=',$weekStart)->whereNull('deleted_at')->count();
    $salesWeekAmount = (float)\DB::table('pishfactors')->where('organization_id',$orgId)
        ->where('created_at','>=',$weekStart)->whereNull('deleted_at')
        ->sum(\DB::raw("REPLACE(fullPrice, ',', '')"));

    $salesMonthCount  = $invoiceCount;
    $salesMonthAmount = $revenue;

    /* ── Hourly (today) ── */
    $hourlyRaw = \DB::table('pishfactors')->where('organization_id',$orgId)
        ->where('created_at','>=',$today)->whereNull('deleted_at')
        ->select([\DB::raw('HOUR(created_at) as hr'), \DB::raw('COUNT(*) as cnt')])
        ->groupBy(\DB::raw('HOUR(created_at)'))->get()->keyBy('hr');
    $hourlyData = [];
    for ($h=6;$h<=22;$h++) $hourlyData[] = (int)($hourlyRaw[$h]->cnt ?? 0);
    $hourlyLabels = array_map(fn($h)=>"$h:00", range(6,22));

    /* ── Daily (this week) ── */
    $weeklyRaw = \DB::table('pishfactors')->where('organization_id',$orgId)
        ->where('created_at','>=',$weekStart)->whereNull('deleted_at')
        ->select([\DB::raw('DATE(created_at) as dy'), \DB::raw('COUNT(*) as cnt')])
        ->groupBy(\DB::raw('DATE(created_at)'))->get()->keyBy('dy');
    $weeklyData=[]; $weeklyLabels=[];
    $dayNames=['ی','د','س','چ','پ','ج','ش'];
    for($d=0;$d<=6;$d++){
        $dt = now()->startOfWeek()->addDays($d);
        $key = $dt->format('Y-m-d');
        $weeklyData[] = (int)($weeklyRaw[$key]->cnt ?? 0);
        $weeklyLabels[] = $dayNames[$d].' '.$dt->format('d/m');
    }

    /* ── Daily (this month) ── */
    $monthlyRaw = \DB::table('pishfactors')->where('organization_id',$orgId)
        ->where('created_at','>=',$monthStart)->where('created_at','<=',$monthEnd)->whereNull('deleted_at')
        ->select([\DB::raw('DAY(created_at) as dy'), \DB::raw('COUNT(*) as cnt')])
        ->groupBy(\DB::raw('DAY(created_at)'))->get()->keyBy('dy');
    $monthlyData=[]; $monthlyLabels=[];
    $daysInMonth = now()->daysInMonth;
    for($d=1;$d<=$daysInMonth;$d++){
        $monthlyData[] = (int)($monthlyRaw[$d]->cnt ?? 0);
        $monthlyLabels[] = $d;
    }

    /* ══════════════════════════════
       SALES TEAM HIERARCHY
    ══════════════════════════════ */
    try {
        $salesTeamUsers = \DB::table('users as u')
            ->join('model_has_roles as mhr', fn($j)=>
                $j->on('mhr.model_id','=','u.id')->where('mhr.model_type','App\Models\User'))
            ->join('roles as r','r.id','=','mhr.role_id')
            ->whereIn('r.title',['sales_manager','leader','visitor'])
            ->whereRaw("u.organization_id LIKE ?", ["%{$orgId}%"])
            ->whereNull('u.deleted_at')->where('u.isActive',1)
            ->select(['u.id','u.name','u.leader_id','r.title as role'])
            ->distinct()->get();

        /* Invoice stats this month */
        $ldStats = \DB::table('pishfactors')->where('organization_id',$orgId)
            ->where('created_at','>=',$monthStart)->whereNull('deleted_at')
            ->select(['sarparast_id',\DB::raw('COUNT(*) as ic'),
                \DB::raw("SUM(REPLACE(fullPrice,',','')) as ts")])
            ->groupBy('sarparast_id')->get()->keyBy('sarparast_id');

        $vsStats = \DB::table('pishfactors')->where('organization_id',$orgId)
            ->where('created_at','>=',$monthStart)->whereNull('deleted_at')
            ->select(['visitor_id',\DB::raw('COUNT(*) as ic'),
                \DB::raw("SUM(REPLACE(fullPrice,',','')) as ts")])
            ->groupBy('visitor_id')->get()->keyBy('visitor_id');

        /* Prev month for trend */
        $ldPrev = \DB::table('pishfactors')->where('organization_id',$orgId)
            ->where('created_at','>=',$prevMonthStart)->where('created_at','<=',$prevMonthEnd)
            ->whereNull('deleted_at')
            ->select(['sarparast_id',\DB::raw("SUM(REPLACE(fullPrice,',','')) as ts")])
            ->groupBy('sarparast_id')->get()->keyBy('sarparast_id');

        $vsPrev = \DB::table('pishfactors')->where('organization_id',$orgId)
            ->where('created_at','>=',$prevMonthStart)->where('created_at','<=',$prevMonthEnd)
            ->whereNull('deleted_at')
            ->select(['visitor_id',\DB::raw("SUM(REPLACE(fullPrice,',','')) as ts")])
            ->groupBy('visitor_id')->get()->keyBy('visitor_id');

        /* Customer counts */
        $custByCreator = \DB::table('customers')->where('organization_id',$orgId)->whereNull('deleted_at')
            ->select(['created_by',\DB::raw('COUNT(*) as cnt')])->groupBy('created_by')
            ->get()->keyBy('created_by');
        $custByLeader = \DB::table('customers')->where('organization_id',$orgId)->whereNull('deleted_at')
            ->select(['leader_id',\DB::raw('COUNT(*) as cnt')])->groupBy('leader_id')
            ->get()->keyBy('leader_id');

        /* Build hierarchy map */
        $teamById = $salesTeamUsers->keyBy('id');
        $salesManagers = $salesTeamUsers->where('role','sales_manager');
        $leaders       = $salesTeamUsers->where('role','leader');
        $visitors      = $salesTeamUsers->where('role','visitor');

    } catch (\Throwable $_) {
        $salesTeamUsers=collect([]); $ldStats=collect([]); $vsStats=collect([]);
        $ldPrev=collect([]); $vsPrev=collect([]); $custByCreator=collect([]); $custByLeader=collect([]);
        $salesManagers=collect([]); $leaders=collect([]); $visitors=collect([]);
        $teamById=collect([]);
    }

    /* ══════════════════════════════
       TARGETS
    ══════════════════════════════ */
    try {
        $activeTargets = \DB::table('targets as t')
            ->join('users as u','u.id','=','t.user_id')
            ->where('t.status',1)
            ->whereRaw("t.organization_id LIKE ?", ["%{$orgId}%"])
            ->select(['t.id','t.user_id','t.target_price','t.start_date_en','t.end_date_en','u.name as uname'])
            ->orderBy('t.id','desc')->limit(8)->get();

        $targetUserIds = $activeTargets->pluck('user_id')->unique()->toArray();

        /* Actual sales per user in target period — one query */
        $targetActuals = \DB::table('pishfactors')
            ->where('organization_id',$orgId)->whereNull('deleted_at')
            ->where(fn($q)=>$q->whereIn('visitor_id',$targetUserIds)->orWhereIn('sarparast_id',$targetUserIds))
            ->select(['visitor_id','sarparast_id',\DB::raw("REPLACE(fullPrice,',','') as fp"),'created_at'])
            ->get();

        foreach ($activeTargets as $tgt) {
            $tp = (float)str_replace(',','',$tgt->target_price ?? 0);
            $tgt->target_price_num = $tp;
            $s = $e = null;
            try { $s = \Carbon\Carbon::parse($tgt->start_date_en); $e = \Carbon\Carbon::parse($tgt->end_date_en); }
            catch(\Throwable $_) {}
            $actual = $targetActuals
                ->filter(fn($r)=>
                    ($r->visitor_id == $tgt->user_id || $r->sarparast_id == $tgt->user_id)
                    && (!$s || \Carbon\Carbon::parse($r->created_at)->gte($s))
                    && (!$e || \Carbon\Carbon::parse($r->created_at)->lte($e))
                )->sum(fn($r)=>(float)$r->fp);
            $tgt->actual = $actual;
            $tgt->pct_done = $tp > 0 ? min(100, round($actual/$tp*100,1)) : 0;
        }
    } catch (\Throwable $_) { $activeTargets = collect([]); }

    /* ══════════════════════════════
       TOP PRODUCTS THIS MONTH (product list card)
    ══════════════════════════════ */
    try {
        $topProducts = \DB::table('pish_factor_items')
            ->join('pishfactors', 'pish_factor_items.factor_id', '=', 'pishfactors.id')
            ->join('products', 'pish_factor_items.product_id', '=', 'products.id')
            ->where('pishfactors.organization_id', $orgId)
            ->whereMonth('pishfactors.created_at', now()->month)
            ->whereYear('pishfactors.created_at', now()->year)
            ->whereNull('pishfactors.deleted_at')
            ->select('products.name as pname',
                \DB::raw('SUM(pish_factor_items.count) as total_sold'),
                \DB::raw('SUM(pish_factor_items.fullPrice) as total_revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(10)->get();
        $topProductsTotal = max(1, (float)$topProducts->sum('total_revenue'));
    } catch (\Throwable $_) {
        try {
            $topProducts = \DB::table('pish_factor_items as pfi')
                ->join('pishfactors as pf','pf.id','=','pfi.pishfactor_id')
                ->join('products as p','p.id','=','pfi.pr_id')
                ->where('pf.organization_id',$orgId)
                ->where('pf.created_at','>=',$monthStart)->whereNull('pf.deleted_at')
                ->select(['p.title as pname',
                    \DB::raw('COUNT(DISTINCT pf.id) as total_sold'),
                    \DB::raw('SUM(pfi.line_total) as total_revenue')])
                ->groupBy('pfi.pr_id','p.title')
                ->orderBy('total_revenue','desc')->limit(10)->get();
            $topProductsTotal = max(1,(float)$topProducts->sum('total_revenue'));
        } catch (\Throwable $_) { $topProducts=collect([]); $topProductsTotal=1; }
    }

    /* ══════════════════════════════
       SUPERVISORS (leaders) THIS MONTH
    ══════════════════════════════ */
    try {
        $supervisorRows = \DB::table('users as u')
            ->join('model_has_roles as mhr', fn($j) =>
                $j->on('mhr.model_id','=','u.id')->where('mhr.model_type','App\Models\User'))
            ->join('roles as r','r.id','=','mhr.role_id')
            ->where('r.title','leader')
            ->whereRaw("u.organization_id LIKE ?", ["%{$orgId}%"])
            ->whereNull('u.deleted_at')->where('u.isActive',1)
            ->select('u.id','u.name')
            ->distinct()->get();

        $supIds = $supervisorRows->pluck('id')->toArray();

        $subCounts = \DB::table('users')
            ->whereIn('leader_id', $supIds)->whereNull('deleted_at')->where('isActive',1)
            ->select('leader_id',\DB::raw('COUNT(*) as cnt'))
            ->groupBy('leader_id')->get()->keyBy('leader_id');

        $custByLeaderSup = \DB::table('customers')
            ->where('organization_id',$orgId)->whereNull('deleted_at')
            ->whereIn('leader_id',$supIds)
            ->select('leader_id',\DB::raw('COUNT(*) as cnt'))
            ->groupBy('leader_id')->get()->keyBy('leader_id');

        $ldInvStats = \DB::table('pishfactors')
            ->where('organization_id',$orgId)->whereNull('deleted_at')
            ->where('created_at','>=',$monthStart)
            ->whereIn('sarparast_id',$supIds)
            ->select('sarparast_id',
                \DB::raw('COUNT(*) as inv_count'),
                \DB::raw("SUM(REPLACE(fullPrice,',','')) as total_sales"))
            ->groupBy('sarparast_id')->get()->keyBy('sarparast_id');
    } catch (\Throwable $_) {
        $supervisorRows=collect([]); $subCounts=collect([]);
        $custByLeaderSup=collect([]); $ldInvStats=collect([]);
    }

    /* ══════════════════════════════
       MARKETERS (visitors/experts) THIS MONTH
    ══════════════════════════════ */
    try {
        $marketerRows = \DB::table('users as u')
            ->join('model_has_roles as mhr', fn($j) =>
                $j->on('mhr.model_id','=','u.id')->where('mhr.model_type','App\Models\User'))
            ->join('roles as r','r.id','=','mhr.role_id')
            ->whereIn('r.title',['visitor','expert','bazaryab'])
            ->whereRaw("u.organization_id LIKE ?", ["%{$orgId}%"])
            ->whereNull('u.deleted_at')->where('u.isActive',1)
            ->select('u.id','u.name','u.leader_id')
            ->distinct()->get();

        $mktIds = $marketerRows->pluck('id')->toArray();

        $custByMkt = \DB::table('customers')
            ->where('organization_id',$orgId)->whereNull('deleted_at')
            ->whereIn('created_by',$mktIds)
            ->select('created_by',\DB::raw('COUNT(*) as cnt'))
            ->groupBy('created_by')->get()->keyBy('created_by');

        $mktInvStats = \DB::table('pishfactors')
            ->where('organization_id',$orgId)->whereNull('deleted_at')
            ->where('created_at','>=',$monthStart)
            ->whereIn('visitor_id',$mktIds)
            ->select('visitor_id',
                \DB::raw('COUNT(*) as inv_count'),
                \DB::raw("SUM(REPLACE(fullPrice,',','')) as total_sales"))
            ->groupBy('visitor_id')->get()->keyBy('visitor_id');

        $leaderNames = \DB::table('users')
            ->whereIn('id', $marketerRows->pluck('leader_id')->filter()->unique()->toArray())
            ->pluck('name','id');
    } catch (\Throwable $_) {
        $marketerRows=collect([]); $custByMkt=collect([]);
        $mktInvStats=collect([]); $leaderNames=collect([]);
    }

    /* ══════════════════════════════
       TRANSPORT / DELIVERY CHECK
    ══════════════════════════════ */
    try {
        $hasTransport = \DB::table('cargos')->where('organization_id', $orgId)->exists();
        if ($hasTransport) {
            $transportStats = \DB::table('cargos as c')
                ->join('users as u','u.id','=','c.driver_id')
                ->where('c.organization_id',$orgId)
                ->select('u.name as driver_name',
                    \DB::raw('COUNT(c.id) as order_count'),
                    \DB::raw('MAX(c.status) as status'))
                ->groupBy('u.id','u.name')
                ->orderByDesc('order_count')->limit(10)->get();
        } else {
            $transportStats = collect([]);
        }
    } catch (\Throwable $_) { $hasTransport=false; $transportStats=collect([]); }

    /* ══════════════════════════════
       FINANCIAL SUMMARY
    ══════════════════════════════ */
    try {
        $totalDebit  = (float)\DB::table('vouchers')->where('organization_id',$orgId)
            ->where('created_at','>=',$monthStart)->whereNull('deleted_at')->sum('total_debit');
        $totalCredit = (float)\DB::table('vouchers')->where('organization_id',$orgId)
            ->where('created_at','>=',$monthStart)->whereNull('deleted_at')->sum('total_credit');
        $hasFinancialData = $totalDebit>0 || $totalCredit>0;
    } catch (\Throwable $_) { $totalDebit=0; $totalCredit=0; $hasFinancialData=false; }

    /* ══════════════════════════════
       CRM SUMMARY
    ══════════════════════════════ */
    $crmToday = \DB::table('customers')->where('organization_id',$orgId)
        ->whereNull('deleted_at')->where('created_at','>=',$today)->count();
    $crmWeek  = \DB::table('customers')->where('organization_id',$orgId)
        ->whereNull('deleted_at')->where('created_at','>=',$weekStart)->count();
    $crmMonth = \DB::table('customers')->where('organization_id',$orgId)
        ->whereNull('deleted_at')->where('created_at','>=',$monthStart)->count();

    try {
        $activeThisMonthCustomerIds = \DB::table('pishfactors')->where('organization_id',$orgId)
            ->where('created_at','>=',$monthStart)->whereNull('deleted_at')
            ->distinct()->pluck('customer_id');
        $inactiveCustomers = \DB::table('customers')->where('organization_id',$orgId)
            ->whereNull('deleted_at')->whereNotIn('id',$activeThisMonthCustomerIds)->count();
    } catch (\Throwable $_) { $inactiveCustomers = 0; }

    try {
        $topCustomers = \DB::table('pishfactors as pf')
            ->join('customers as c','c.id','=','pf.customer_id')
            ->where('pf.organization_id',$orgId)
            ->where('pf.created_at','>=',$monthStart)->whereNull('pf.deleted_at')
            ->select(['c.name',\DB::raw('COUNT(pf.id) as ic'),
                \DB::raw("SUM(REPLACE(pf.fullPrice,',','')) as tot")])
            ->groupBy('c.id','c.name')->orderBy('tot','desc')->limit(3)->get();
    } catch (\Throwable $_) { $topCustomers=collect([]); }

    /* ══════════════════════════════
       ENHANCED RECENT CUSTOMERS
    ══════════════════════════════ */
    try {
        $recentCustomers = \DB::table('customers as c')
            ->leftJoin('users as u','u.id','=','c.created_by')
            ->where('c.organization_id',$orgId)->whereNull('c.deleted_at')
            ->orderBy('c.created_at','desc')->limit(10)
            ->select(['c.id','c.name','c.mobile','c.created_at','u.name as marketer'])
            ->get();

        $rcIds = $recentCustomers->pluck('id')->toArray();
        $rcInvCounts = empty($rcIds) ? collect([]) :
            \DB::table('pishfactors')->where('organization_id',$orgId)
                ->whereNull('deleted_at')->whereIn('customer_id',$rcIds)
                ->select(['customer_id',\DB::raw('COUNT(*) as cnt')])
                ->groupBy('customer_id')->get()->keyBy('customer_id');
    } catch (\Throwable $_) { $recentCustomers=collect([]); $rcInvCounts=collect([]); }

    /* ══════════════════════════════
       DRIVERS
    ══════════════════════════════ */
    try {
        $driverCount = \DB::table('users as u')
            ->join('model_has_roles as mhr',fn($j)=>
                $j->on('mhr.model_id','=','u.id')->where('mhr.model_type','App\Models\User'))
            ->join('roles as r','r.id','=','mhr.role_id')
            ->where('r.title','driver')
            ->whereRaw("u.organization_id LIKE ?", ["%{$orgId}%"])
            ->whereNull('u.deleted_at')->where('u.isActive',1)
            ->distinct()->count('u.id');
    } catch (\Throwable $_) { $driverCount=0; }

} catch (\Throwable $e) {
    /* ── Global failsafe ── */
    $nf=fn($n)=>number_format((int)$n);
    $nfT=fn($n)=>number_format((int)($n/10));
    $pct=fn($a,$b)=>0;
    $updatedAt=$orgName='دارمینو'; $orgId=0;
    $today=$weekStart=$monthStart=now(); $monthEnd=$prevMonthStart=$prevMonthEnd=now();
    $customerCount=$invoiceCount=$revenue=$personnelCount=0;
    $activeProductCount=$stockTotal=0; $hasStocks=false;
    $salesTodayCount=$salesTodayAmount=$salesWeekCount=$salesWeekAmount=0;
    $salesMonthCount=$salesMonthAmount=0;
    $hourlyData=array_fill(0,17,0); $hourlyLabels=[];
    $weeklyData=array_fill(0,7,0); $weeklyLabels=[];
    $monthlyData=[]; $monthlyLabels=[];
    $salesTeamUsers=$ldStats=$vsStats=$ldPrev=$vsPrev=collect([]);
    $custByCreator=$custByLeader=$salesManagers=$leaders=$visitors=collect([]);
    $teamById=collect([]);
    $activeTargets=collect([]);
    $topProducts=collect([]); $topProductsTotal=1;
    $supervisorRows=collect([]); $subCounts=collect([]); $custByLeaderSup=collect([]); $ldInvStats=collect([]);
    $marketerRows=collect([]); $custByMkt=collect([]); $mktInvStats=collect([]); $leaderNames=collect([]);
    $hasTransport=false; $transportStats=collect([]);
    $totalDebit=$totalCredit=0; $hasFinancialData=false;
    $crmToday=$crmWeek=$crmMonth=$inactiveCustomers=0;
    $topCustomers=$recentCustomers=collect([]); $rcInvCounts=collect([]);
    $driverCount=0;
}

/* ── Role display helpers ── */
$roleLabel = ['sales_manager'=>'مدیر فروش','leader'=>'سرپرست','visitor'=>'بازاریاب',
    'driver'=>'راننده','expert'=>'کارشناس','accountant'=>'حسابدار'];
$roleBadge = ['sales_manager'=>'danger','leader'=>'warning','visitor'=>'primary',
    'driver'=>'info','expert'=>'success'];
@endphp

<body>
@include('sweetalert::alert')

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('sections/sidebar')
        <div class="layout-page">
            @include('sections/header')
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    {{-- ══ WELCOME BANNER ══ --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card" style="background:linear-gradient(135deg,#7367f0 0%,#9e95f5 100%);color:#fff;position:relative;overflow:hidden;min-height:140px;">
                                <div class="card-body py-3 ps-4" style="max-width:72%">
                                    <small class="opacity-75 d-block mb-1">
                                        <i class="ti ti-clock me-1"></i>{{ $updatedAt }} — آخرین به‌روزرسانی
                                    </small>
                                    <h5 class="mb-1 fw-bold" style="color:#fff">خوش آمدید به داشبورد مدیر پنل</h5>
                                    <p class="mb-3 opacity-80" style="font-size:.9rem">
                                        <i class="ti ti-building-store me-1"></i>{{ $orgName }}
                                    </p>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('index') }}" class="btn btn-sm fw-semibold" style="background:#fff;color:#7367f0">
                                            <i class="ti ti-chart-bar me-1"></i> گزارش فروش
                                        </a>
                                        <a href="{{ route('invoices.all_invoices') }}" class="btn btn-sm btn-outline-light">
                                            <i class="ti ti-file-invoice me-1"></i> همه فاکتورها
                                        </a>
                                        @if(\Illuminate\Support\Facades\Route::has('settings.dashboardWidgets'))
                                        <a href="{{ route('settings.dashboardWidgets') }}" class="btn btn-sm btn-outline-light">
                                            <i class="ti ti-settings me-1"></i> تنظیمات
                                        </a>
                                        @endif
                                    </div>
                                </div>
                                {{-- Custom Dokan ERP brand SVG --}}
                                <div class="welcome-svg-wrap">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 155" fill="none">
                                        <circle cx="100" cy="90" r="72" fill="white" opacity="0.06"/>
                                        <!-- Building body -->
                                        <rect x="45" y="72" width="90" height="75" rx="4" fill="white" opacity="0.13"/>
                                        <!-- Windows row 1 -->
                                        <rect x="54" y="80" width="22" height="18" rx="2" fill="#FFD700" opacity="0.82"/>
                                        <rect x="83" y="80" width="22" height="18" rx="2" fill="#FFD700" opacity="0.82"/>
                                        <rect x="104" y="80" width="22" height="18" rx="2" fill="#FFD700" opacity="0.82"/>
                                        <!-- Door -->
                                        <rect x="77" y="113" width="26" height="34" rx="3" fill="#FFD700" opacity="0.65"/>
                                        <!-- Roof -->
                                        <polygon points="40,72 160,72 100,38" fill="white" opacity="0.18"/>
                                        <!-- Roof accent -->
                                        <polygon points="60,72 140,72 100,48" fill="white" opacity="0.08"/>
                                        <!-- Chart bars -->
                                        <rect x="162" y="112" width="9" height="35" rx="2" fill="#FFD700" opacity="0.75"/>
                                        <rect x="174" y="94" width="9" height="53" rx="2" fill="#FFD700" opacity="0.88"/>
                                        <rect x="186" y="78" width="9" height="69" rx="2" fill="#FFD700"/>
                                        <!-- Trend arrow -->
                                        <polyline points="167,112 179,94 191,78" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" opacity="0.8"/>
                                        <!-- Label -->
                                        <text x="90" y="30" text-anchor="middle" font-family="Tahoma,sans-serif" font-size="14" font-weight="bold" fill="white" opacity="0.95">دکان ERP</text>
                                        <!-- Stars -->
                                        <circle cx="28" cy="50" r="2.5" fill="white" opacity="0.4"/>
                                        <circle cx="168" cy="28" r="2" fill="#FFD700" opacity="0.5"/>
                                        <circle cx="18" cy="100" r="2" fill="#FFD700" opacity="0.4"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ══ STAT CARDS (مشتریان / پرسنل / محصولات / انبار) ══ --}}
                    {{-- فاکتور ماه و فروش ماه از اینجا حذف شدند — در بخش آمار فروش موجودند --}}
                    <div class="row g-4 mb-4">

                        {{-- مشتریان --}}
                        <div class="col-6 col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="stat-icon" style="background:rgba(115,103,240,.12)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7367f0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                        </div>
                                        <span class="text-muted small fw-semibold">مشتریان</span>
                                    </div>
                                    <h3 class="mb-0 fw-bold">{{ $nf($customerCount) }}</h3>
                                    <p class="text-muted small mb-0 mt-1">مشتری ثبت‌شده</p>
                                </div>
                            </div>
                        </div>

                        {{-- پرسنل --}}
                        <div class="col-6 col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="stat-icon" style="background:rgba(0,207,232,.12)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#00cfe8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                                <path d="M8 21h8M12 17v4"/>
                                                <circle cx="9" cy="9" r="2"/>
                                                <path d="M15 8h2M15 11h2"/>
                                            </svg>
                                        </div>
                                        <span class="text-muted small fw-semibold">پرسنل</span>
                                    </div>
                                    <h3 class="mb-0 fw-bold">{{ $nf($personnelCount) }}</h3>
                                    <p class="text-muted small mb-0 mt-1">نفر فعال</p>
                                </div>
                            </div>
                        </div>

                        {{-- محصولات --}}
                        <div class="col-6 col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="stat-icon" style="background:rgba(234,84,85,.12)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea5455" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                                <line x1="12" y1="22.08" x2="12" y2="12"/>
                                            </svg>
                                        </div>
                                        <span class="text-muted small fw-semibold">محصولات</span>
                                    </div>
                                    <h3 class="mb-0 fw-bold">{{ $nf($activeProductCount) }}</h3>
                                    <p class="text-muted small mb-0 mt-1">محصول فعال</p>
                                </div>
                            </div>
                        </div>

                        {{-- انبار (فقط وقتی موجودی دارد) --}}
                        @if($hasStocks)
                        <div class="col-6 col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="stat-icon" style="background:rgba(115,103,240,.12)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7367f0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35z"/>
                                                <path d="M6 18h12M6 14h12M2 10h20"/>
                                            </svg>
                                        </div>
                                        <span class="text-muted small fw-semibold">انبار</span>
                                    </div>
                                    <h3 class="mb-0 fw-bold">{{ $nf($stockTotal) }}</h3>
                                    <p class="text-muted small mb-0 mt-1">موجودی کل</p>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>{{-- /stat cards --}}

                    {{-- ══ SALES TODAY/WEEK/MONTH TABS ══ --}}
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-trending-up text-primary me-2"></i>
                                        آمار فروش
                                    </h5>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm tab-sales-btn active" data-tab="today">امروز</button>
                                        <button class="btn btn-sm btn-outline-secondary tab-sales-btn" data-tab="week">این هفته</button>
                                        <button class="btn btn-sm btn-outline-secondary tab-sales-btn" data-tab="month">این ماه</button>
                                    </div>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6 col-md-3">
                                            <div class="crm-mini-stat bg-label-primary">
                                                <div class="text-muted small mb-1">تعداد فاکتور</div>
                                                <h5 class="mb-0 fw-bold text-primary" id="sales-count">{{ $nf($salesTodayCount) }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="crm-mini-stat bg-label-success">
                                                <div class="text-muted small mb-1">مبلغ فروش (تومان)</div>
                                                <h5 class="mb-0 fw-bold text-success" id="sales-amount">{{ $nfT($salesTodayAmount) }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="pm-sales-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ══ SALES TEAM HIERARCHY + TARGETS (side-by-side) ══ --}}
                    <div class="row g-4 mb-4">

                        {{-- Sales Team --}}
                        <div class="col-12 col-xl-7">
                            <div class="card h-100">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-sitemap text-warning me-2"></i>
                                        تیم فروش — سلسله‌مراتب
                                    </h5>
                                    <small class="text-muted">آمار این ماه</small>
                                </div>
                                <div class="card-body p-0" style="max-height:480px;overflow-y:auto">
                                    @if($salesTeamUsers->isEmpty())
                                        <div class="text-center text-muted py-5">
                                            <i class="ti ti-users-group" style="font-size:2rem"></i>
                                            <p class="mt-2 mb-0">اطلاعات تیم فروش یافت نشد</p>
                                        </div>
                                    @else
                                    <div class="accordion accordion-flush" id="teamAccordion">
                                        @foreach($salesManagers as $sm)
                                        @php
                                            $smInvCount = (int)($ldStats[$sm->id]->ic ?? $vsStats[$sm->id]->ic ?? 0);
                                            $smSales    = (float)($ldStats[$sm->id]->ts ?? $vsStats[$sm->id]->ts ?? 0);
                                            $smCust     = (int)($custByLeader[$sm->id]->cnt ?? $custByCreator[$sm->id]->cnt ?? 0);
                                            $smPrevSales= (float)($ldPrev[$sm->id]->ts ?? $vsPrev[$sm->id]->ts ?? 0);
                                            $smTrend    = $pct($smSales, $smPrevSales);
                                            $smLeaders  = $leaders->where('leader_id',$sm->id);
                                        @endphp
                                        <div class="accordion-item border-0 border-bottom">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed py-2 px-3" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#sm-{{ $sm->id }}">
                                                    <div class="d-flex align-items-center gap-2 w-100">
                                                        <span class="badge bg-{{ $roleBadge['sales_manager'] ?? 'secondary' }} team-role-badge">
                                                            {{ $roleLabel['sales_manager'] }}
                                                        </span>
                                                        <strong>{{ $sm->name }}</strong>
                                                        <span class="ms-auto me-2 text-muted small">
                                                            {{ $nf($smInvCount) }} فاکتور
                                                            @if($smTrend > 0)
                                                                <span class="trend-up ms-1"><i class="ti ti-trending-up"></i>{{ $smTrend }}%</span>
                                                            @elseif($smTrend < 0)
                                                                <span class="trend-down ms-1"><i class="ti ti-trending-down"></i>{{ abs($smTrend) }}%</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="sm-{{ $sm->id }}" class="accordion-collapse collapse" data-bs-parent="#teamAccordion">
                                                <div class="accordion-body py-2 px-3">
                                                    {{-- SM stats --}}
                                                    <div class="d-flex gap-3 mb-2 flex-wrap small">
                                                        <span><i class="ti ti-users text-primary me-1"></i>{{ $nf($smCust) }} مشتری</span>
                                                        <span><i class="ti ti-receipt text-success me-1"></i>{{ $nf($smInvCount) }} فاکتور</span>
                                                        <span><i class="ti ti-coin text-warning me-1"></i>{{ $nfT($smSales) }} تومان</span>
                                                    </div>
                                                    {{-- Leaders under this SM --}}
                                                    @foreach($smLeaders as $ld)
                                                    @php
                                                        $ldInvCount = (int)($ldStats[$ld->id]->ic ?? 0);
                                                        $ldSales    = (float)($ldStats[$ld->id]->ts ?? 0);
                                                        $ldCust     = (int)($custByLeader[$ld->id]->cnt ?? 0);
                                                        $ldPrevSales= (float)($ldPrev[$ld->id]->ts ?? 0);
                                                        $ldTrend    = $pct($ldSales,$ldPrevSales);
                                                        $ldVisitors = $visitors->where('leader_id',$ld->id);
                                                    @endphp
                                                    <div class="team-indent-1 mb-2 py-1">
                                                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                            <span class="badge bg-{{ $roleBadge['leader'] ?? 'warning' }} team-role-badge">{{ $roleLabel['leader'] }}</span>
                                                            <span class="fw-semibold">{{ $ld->name }}</span>
                                                            <span class="ms-auto text-muted small">
                                                                {{ $nf($ldInvCount) }} فاکتور —
                                                                {{ $nfT($ldSales) }} ت
                                                                @if($ldTrend>0)<span class="trend-up"><i class="ti ti-arrow-up-right"></i>{{ $ldTrend }}%</span>
                                                                @elseif($ldTrend<0)<span class="trend-down"><i class="ti ti-arrow-down-right"></i>{{ abs($ldTrend) }}%</span>@endif
                                                            </span>
                                                        </div>
                                                        {{-- Visitors under this leader --}}
                                                        @foreach($ldVisitors as $vs)
                                                        @php
                                                            $vsInvCount = (int)($vsStats[$vs->id]->ic ?? 0);
                                                            $vsSales    = (float)($vsStats[$vs->id]->ts ?? 0);
                                                            $vsCust     = (int)($custByCreator[$vs->id]->cnt ?? 0);
                                                            $vsPrevSl   = (float)($vsPrev[$vs->id]->ts ?? 0);
                                                            $vsTrend    = $pct($vsSales,$vsPrevSl);
                                                        @endphp
                                                        <div class="team-indent-2 py-1">
                                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                <span class="badge bg-label-primary team-role-badge">{{ $roleLabel['visitor'] }}</span>
                                                                <span>{{ $vs->name }}</span>
                                                                <span class="ms-auto text-muted small">
                                                                    {{ $nf($vsCust) }}م · {{ $nf($vsInvCount) }}ف ·
                                                                    <strong>{{ $nfT($vsSales) }}ت</strong>
                                                                    @if($vsTrend>0)<span class="trend-up ms-1">↑{{ $vsTrend }}%</span>
                                                                    @elseif($vsTrend<0)<span class="trend-down ms-1">↓{{ abs($vsTrend) }}%</span>@endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @endforeach

                                                    {{-- Visitors directly under SM (no leader) --}}
                                                    @foreach($visitors->where('leader_id',$sm->id) as $vs)
                                                    @php
                                                        $vsInvCount=(int)($vsStats[$vs->id]->ic??0);
                                                        $vsSales=(float)($vsStats[$vs->id]->ts??0);
                                                        $vsCust=(int)($custByCreator[$vs->id]->cnt??0);
                                                    @endphp
                                                    <div class="team-indent-1 py-1">
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <span class="badge bg-label-primary team-role-badge">{{ $roleLabel['visitor'] }}</span>
                                                            <span>{{ $vs->name }}</span>
                                                            <span class="ms-auto text-muted small">
                                                                {{ $nf($vsCust) }}م · {{ $nf($vsInvCount) }}ف · {{ $nfT($vsSales) }}ت
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach

                                        {{-- Leaders with no SM --}}
                                        @foreach($leaders->whereNotIn('leader_id', $salesManagers->pluck('id')->toArray()) as $ld)
                                        @php
                                            $ldInvCount=(int)($ldStats[$ld->id]->ic??0);
                                            $ldSales=(float)($ldStats[$ld->id]->ts??0);
                                            $ldCust=(int)($custByLeader[$ld->id]->cnt??0);
                                        @endphp
                                        <div class="accordion-item border-0 border-bottom">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed py-2 px-3" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#ld-root-{{ $ld->id }}">
                                                    <span class="badge bg-warning team-role-badge me-2">{{ $roleLabel['leader'] }}</span>
                                                    <strong>{{ $ld->name }}</strong>
                                                    <span class="ms-auto me-2 text-muted small">{{ $nf($ldInvCount) }} فاکتور</span>
                                                </button>
                                            </h2>
                                            <div id="ld-root-{{ $ld->id }}" class="accordion-collapse collapse">
                                                <div class="accordion-body py-2 px-3">
                                                    <div class="d-flex gap-3 mb-2 flex-wrap small">
                                                        <span><i class="ti ti-users text-primary me-1"></i>{{ $nf($ldCust) }} مشتری</span>
                                                        <span><i class="ti ti-receipt text-success me-1"></i>{{ $nf($ldInvCount) }} فاکتور</span>
                                                        <span><i class="ti ti-coin text-warning me-1"></i>{{ $nfT($ldSales) }} تومان</span>
                                                    </div>
                                                    @foreach($visitors->where('leader_id',$ld->id) as $vs)
                                                    @php $vsInvCount=(int)($vsStats[$vs->id]->ic??0); $vsSales=(float)($vsStats[$vs->id]->ts??0); @endphp
                                                    <div class="team-indent-2 py-1">
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <span class="badge bg-label-primary team-role-badge">{{ $roleLabel['visitor'] }}</span>
                                                            <span>{{ $vs->name }}</span>
                                                            <span class="ms-auto text-muted small">{{ $nf($vsInvCount) }}ف · {{ $nfT($vsSales) }}ت</span>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Targets --}}
                        <div class="col-12 col-xl-5">
                            <div class="card h-100">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-target text-danger me-2"></i>
                                        تارگت‌های فروش
                                    </h5>
                                </div>
                                <div class="card-body" style="max-height:480px;overflow-y:auto">
                                    @if($activeTargets->isEmpty())
                                        <div class="text-center py-4">
                                            <i class="ti ti-target-off text-muted" style="font-size:2.5rem"></i>
                                            <p class="text-muted mt-2">تارگت فروش تعریف نشده</p>
                                            @if(\Illuminate\Support\Facades\Route::has('settings.dashboardWidgets'))
                                            <a href="{{ route('settings.dashboardWidgets') }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-settings me-1"></i> تنظیم تارگت
                                            </a>
                                            @endif
                                        </div>
                                    @else
                                    @foreach($activeTargets as $tgt)
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold small">{{ $tgt->uname }}</span>
                                            <span class="badge {{ $tgt->pct_done >= 100 ? 'bg-success' : ($tgt->pct_done >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                                {{ $tgt->pct_done }}%
                                            </span>
                                        </div>
                                        <div class="target-bar mb-1">
                                            <div class="target-bar-fill {{ $tgt->pct_done >= 100 ? 'bg-success' : ($tgt->pct_done >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                                style="width:{{ $tgt->pct_done }}%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted" style="font-size:.75rem">
                                            <span>واقعی: <strong>{{ $nfT($tgt->actual) }}</strong> ت</span>
                                            <span>هدف: {{ $nfT($tgt->target_price_num) }} ت</span>
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>{{-- /team + targets --}}

                    {{-- ══ TOP PRODUCTS + FINANCIAL ══ --}}
                    <div class="row g-4 mb-4">

                        {{-- Top Products — Vuexy-style product list card --}}
                        <div class="col-12 col-lg-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header border-bottom d-flex align-items-center justify-content-between" style="background:linear-gradient(90deg,#f8f7ff 0%,#fff 100%)">
                                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#7367f0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                                        </svg>
                                        پرفروش‌ترین محصولات این ماه
                                    </h5>
                                    <span class="badge bg-label-primary rounded-pill">{{ $topProducts->count() }}</span>
                                </div>
                                <div class="card-body p-0">
                                    @if($topProducts->isEmpty())
                                        <div class="text-center py-5">
                                            <i class="ti ti-package-off text-muted" style="font-size:2.5rem"></i>
                                            <p class="text-muted mt-2 mb-0">داده‌ای موجود نیست</p>
                                        </div>
                                    @else
                                    @php $productColors = ['#7367f0','#28c76f','#ff9f43','#00cfe8','#ea5455','#7367f0','#28c76f','#ff9f43','#00cfe8','#ea5455']; @endphp
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" dir="rtl">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-3" style="width:45%">نام محصول</th>
                                                    <th class="text-center">تعداد فروش</th>
                                                    <th class="text-center pe-3">درآمد (تومان)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($topProducts as $idx => $prod)
                                            @php
                                                $color = $productColors[$idx % 10];
                                                $revPct = $topProductsTotal > 0 ? min(100, round((float)$prod->total_revenue / $topProductsTotal * 100)) : 0;
                                            @endphp
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $color }};flex-shrink:0"></div>
                                                        <div>
                                                            <div class="fw-semibold small">{{ $prod->pname }}</div>
                                                            <div class="product-bar mt-1" style="width:70px">
                                                                <div class="product-bar-fill" style="width:{{ $revPct }}%;background:{{ $color }}"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge rounded-pill" style="background:{{ $color }}20;color:{{ $color }}">
                                                        {{ $nf($prod->total_sold) }}
                                                    </span>
                                                </td>
                                                <td class="text-center pe-3 fw-semibold small">
                                                    {{ $nfT((float)$prod->total_revenue) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Financial Summary --}}
                        <div class="col-12 col-lg-6">
                            <div class="card h-100">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-calculator text-success me-2"></i>
                                        خلاصه مالی و حسابداری
                                    </h5>
                                    <small class="text-muted">این ماه</small>
                                </div>
                                <div class="card-body">
                                    @if(!$hasFinancialData)
                                    <div class="alert alert-light border text-center py-4">
                                        <i class="ti ti-receipt-off text-muted mb-2" style="font-size:2rem;display:block"></i>
                                        <p class="mb-0 text-muted">اطلاعات مالی پس از ثبت اسناد حسابداری نمایش داده می‌شود</p>
                                    </div>
                                    @else
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="crm-mini-stat bg-label-success">
                                                <div class="text-muted small mb-1"><i class="ti ti-arrow-down-circle text-success me-1"></i>بستانکار ماه</div>
                                                <h5 class="mb-0 fw-bold text-success">{{ $nfT($totalCredit) }}</h5>
                                                <small class="text-muted">تومان</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="crm-mini-stat bg-label-danger">
                                                <div class="text-muted small mb-1"><i class="ti ti-arrow-up-circle text-danger me-1"></i>بدهکار ماه</div>
                                                <h5 class="mb-0 fw-bold text-danger">{{ $nfT($totalDebit) }}</h5>
                                                <small class="text-muted">تومان</small>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            @php $netMonth = $totalCredit - $totalDebit; @endphp
                                            <div class="crm-mini-stat {{ $netMonth >= 0 ? 'bg-label-success' : 'bg-label-danger' }}">
                                                <div class="text-muted small mb-1">سود / زیان خالص ماه</div>
                                                <h4 class="mb-0 fw-bold {{ $netMonth >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $netMonth >= 0 ? '+' : '' }}{{ $nfT($netMonth) }} تومان
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>{{-- /products + financial --}}

                    {{-- ══ CRM SUMMARY ══ --}}
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-address-book text-info me-2"></i>
                                        خلاصه CRM مشتریان
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6 col-md-3">
                                            <div class="crm-mini-stat bg-label-info text-center">
                                                <div class="text-muted small mb-1">مشتری جدید امروز</div>
                                                <h4 class="mb-0 fw-bold text-info">{{ $nf($crmToday) }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="crm-mini-stat bg-label-primary text-center">
                                                <div class="text-muted small mb-1">مشتری جدید این هفته</div>
                                                <h4 class="mb-0 fw-bold text-primary">{{ $nf($crmWeek) }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="crm-mini-stat bg-label-success text-center">
                                                <div class="text-muted small mb-1">مشتری جدید این ماه</div>
                                                <h4 class="mb-0 fw-bold text-success">{{ $nf($crmMonth) }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="crm-mini-stat bg-label-warning text-center">
                                                <div class="text-muted small mb-1">مشتریان بدون فاکتور ماه</div>
                                                <h4 class="mb-0 fw-bold text-warning">{{ $nf($inactiveCustomers) }}</h4>
                                            </div>
                                        </div>
                                    </div>

                                    @if($topCustomers->isNotEmpty())
                                    <div class="mt-4">
                                        <p class="pm-section-title mb-2"><i class="ti ti-crown me-1"></i>برترین مشتریان این ماه</p>
                                        <div class="row g-3">
                                            @foreach($topCustomers as $idx => $tc)
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex align-items-center gap-2 p-2 bg-light rounded">
                                                    <div class="stat-icon {{ $idx===0?'bg-label-warning':($idx===1?'bg-label-secondary':'bg-label-info') }}" style="width:36px;height:36px;font-size:1rem">
                                                        {{ $idx+1 }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold small">{{ $tc->name }}</div>
                                                        <div class="text-muted" style="font-size:.75rem">{{ $nf($tc->ic) }} فاکتور · {{ $nfT((float)$tc->tot) }} ت</div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ══ ENHANCED RECENT CUSTOMERS ══ --}}
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-users text-success me-2"></i>
                                        آخرین مشتریان ثبت‌شده
                                    </h5>
                                    <span class="badge bg-label-primary rounded-pill">{{ count($recentCustomers) }}</span>
                                </div>
                                <div class="card-body p-0">
                                    @if($recentCustomers->isEmpty())
                                        <p class="text-center text-muted py-5 mb-0">داده‌ای یافت نشد</p>
                                    @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-3">نام مشتری</th>
                                                    <th>موبایل</th>
                                                    <th>بازاریاب</th>
                                                    <th>تعداد فاکتور</th>
                                                    <th class="pe-3">تاریخ ثبت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentCustomers as $cust)
                                                <tr>
                                                    <td class="ps-3 fw-semibold">{{ $cust->name }}</td>
                                                    <td>
                                                        @if(!empty($cust->mobile))
                                                            <span class="badge bg-label-secondary">{{ $cust->mobile }}</span>
                                                        @else <span class="text-muted">—</span>@endif
                                                    </td>
                                                    <td class="small text-muted">{{ $cust->marketer ?? '—' }}</td>
                                                    <td>
                                                        @php $ic = (int)($rcInvCounts[$cust->id]->cnt ?? 0); @endphp
                                                        @if($ic > 0)
                                                            <span class="badge bg-label-success">{{ $nf($ic) }}</span>
                                                        @else
                                                            <span class="text-muted">۰</span>
                                                        @endif
                                                    </td>
                                                    <td class="pe-3 text-muted small">
                                                        {{ verta_date($cust->created_at) }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ══ DRIVERS WIDGET (conditional) ══ --}}
                    @if($driverCount > 0)
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon bg-label-info" style="width:52px;height:52px;font-size:1.5rem">
                                            <i class="ti ti-truck text-info"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold">{{ $nf($driverCount) }}</h4>
                                            <p class="text-muted small mb-0">راننده فعال در سیستم</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ══════════════════════════════════════════════════
                         BOTTOM TABLES: وضعیت سرپرستان فروش / بازاریاب‌ها / حمل‌ونقل
                    ══════════════════════════════════════════════════ --}}

                    {{-- Table A: وضعیت سرپرستان فروش --}}
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header border-bottom d-flex align-items-center gap-2" style="background:linear-gradient(90deg,#fff9e6 0%,#fff 100%)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff9f43" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="8" r="4"/>
                                        <path d="M20 21a8 8 0 1 0-16 0"/>
                                        <path d="M9 13l-2 8 5-3 5 3-2-8"/>
                                    </svg>
                                    <h5 class="card-title mb-0">وضعیت سرپرستان فروش</h5>
                                    <small class="text-muted me-auto">این ماه</small>
                                </div>
                                <div class="card-body p-0">
                                    @if($supervisorRows->isEmpty())
                                        <div class="text-center py-5">
                                            <i class="ti ti-user-star text-muted" style="font-size:2rem"></i>
                                            <p class="text-muted mt-2 mb-0">سرپرست فروشی یافت نشد</p>
                                        </div>
                                    @else
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" dir="rtl">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-4">نام سرپرست</th>
                                                    <th class="text-center">تعداد بازاریاب‌ها</th>
                                                    <th class="text-center">تعداد مشتریان جذب‌شده</th>
                                                    <th class="text-center">تعداد فاکتور</th>
                                                    <th class="text-center pe-4">جمع فروش این ماه (تومان)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($supervisorRows as $sup)
                                            @php
                                                $supSubCount = (int)($subCounts[$sup->id]->cnt ?? 0);
                                                $supCustCount = (int)($custByLeaderSup[$sup->id]->cnt ?? 0);
                                                $supInvCount  = (int)($ldInvStats[$sup->id]->inv_count ?? 0);
                                                $supSales     = (float)($ldInvStats[$sup->id]->total_sales ?? 0);
                                            @endphp
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="stat-icon" style="width:36px;height:36px;background:rgba(255,159,67,.15)">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff9f43" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/>
                                                            </svg>
                                                        </div>
                                                        <span class="fw-semibold">{{ $sup->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-label-info">{{ $nf($supSubCount) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-label-primary">{{ $nf($supCustCount) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-label-success">{{ $nf($supInvCount) }}</span>
                                                </td>
                                                <td class="text-center pe-4 fw-semibold">
                                                    @if($supSales > 0)
                                                        <span class="text-success">{{ $nfT($supSales) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table B: وضعیت بازاریاب‌ها --}}
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header border-bottom d-flex align-items-center gap-2" style="background:linear-gradient(90deg,#f0fff6 0%,#fff 100%)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#28c76f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                    <h5 class="card-title mb-0">وضعیت بازاریاب‌ها</h5>
                                    <small class="text-muted me-auto">این ماه</small>
                                </div>
                                <div class="card-body p-0">
                                    @if($marketerRows->isEmpty())
                                        <div class="text-center py-5">
                                            <i class="ti ti-users text-muted" style="font-size:2rem"></i>
                                            <p class="text-muted mt-2 mb-0">بازاریابی یافت نشد</p>
                                        </div>
                                    @else
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" dir="rtl">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-4">نام بازاریاب</th>
                                                    <th class="text-center">سرپرست</th>
                                                    <th class="text-center">تعداد مشتری</th>
                                                    <th class="text-center">تعداد فاکتور</th>
                                                    <th class="text-center pe-4">جمع فروش این ماه (تومان)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($marketerRows as $mkt)
                                            @php
                                                $mktCust  = (int)($custByMkt[$mkt->id]->cnt ?? 0);
                                                $mktInv   = (int)($mktInvStats[$mkt->id]->inv_count ?? 0);
                                                $mktSales = (float)($mktInvStats[$mkt->id]->total_sales ?? 0);
                                                $mktLeaderName = $mkt->leader_id ? ($leaderNames[$mkt->leader_id] ?? '—') : '—';
                                            @endphp
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="stat-icon" style="width:36px;height:36px;background:rgba(40,199,111,.15)">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#28c76f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                                                <circle cx="12" cy="7" r="4"/>
                                                            </svg>
                                                        </div>
                                                        <span class="fw-semibold">{{ $mkt->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if($mktLeaderName !== '—')
                                                        <span class="badge bg-label-warning">{{ $mktLeaderName }}</span>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-label-primary">{{ $nf($mktCust) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-label-success">{{ $nf($mktInv) }}</span>
                                                </td>
                                                <td class="text-center pe-4 fw-semibold">
                                                    @if($mktSales > 0)
                                                        <span class="text-success">{{ $nfT($mktSales) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table C: وضعیت حمل و نقل (conditional) --}}
                    @if($hasTransport && $transportStats->isNotEmpty())
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header border-bottom d-flex align-items-center gap-2" style="background:linear-gradient(90deg,#e8f8ff 0%,#fff 100%)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00cfe8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="3" width="15" height="13" rx="2"/>
                                        <path d="M16 8h4l3 3v5h-7V8z"/>
                                        <circle cx="5.5" cy="18.5" r="2.5"/>
                                        <circle cx="18.5" cy="18.5" r="2.5"/>
                                    </svg>
                                    <h5 class="card-title mb-0">وضعیت حمل و نقل</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" dir="rtl">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-4">راننده / ناوگان</th>
                                                    <th class="text-center">تعداد سفارش</th>
                                                    <th class="text-center pe-4">وضعیت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($transportStats as $tr)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="stat-icon" style="width:36px;height:36px;background:rgba(0,207,232,.12)">
                                                            <i class="ti ti-truck text-info" style="font-size:.9rem"></i>
                                                        </div>
                                                        <span class="fw-semibold">{{ $tr->driver_name }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-label-info">{{ $nf($tr->order_count) }}</span>
                                                </td>
                                                <td class="text-center pe-4">
                                                    @php
                                                        $statusMap = [0=>'در انتظار',1=>'در حال تحویل',2=>'تحویل شده',3=>'لغو شده'];
                                                        $statusBadge = [0=>'warning',1=>'primary',2=>'success',3=>'danger'];
                                                        $statusKey = (int)($tr->status ?? 0);
                                                    @endphp
                                                    <span class="badge bg-{{ $statusBadge[$statusKey] ?? 'secondary' }}">
                                                        {{ $statusMap[$statusKey] ?? 'نامشخص' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>{{-- /container --}}
                @include('sections/footer')
            </div>{{-- /content-wrapper --}}
        </div>{{-- /layout-page --}}
    </div>{{-- /layout-container --}}
    <div class="layout-overlay layout-menu-toggle"></div>
</div>{{-- /layout-wrapper --}}

{{-- Scripts --}}
<script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<script src="{{ asset('assets/') }}/js/main.js"></script>

<script>
(function () {
    'use strict';

    /* ── PHP → JS data ── */
    var chartData = {
        today:  { labels: {!! json_encode($hourlyLabels) !!}, data: {!! json_encode($hourlyData) !!},  count: {{ $salesTodayCount }},  amount: '{{ $nfT($salesTodayAmount) }}' },
        week:   { labels: {!! json_encode($weeklyLabels) !!},  data: {!! json_encode($weeklyData) !!},  count: {{ $salesWeekCount }},   amount: '{{ $nfT($salesWeekAmount) }}' },
        month:  { labels: {!! json_encode($monthlyLabels) !!}, data: {!! json_encode($monthlyData ?? []) !!}, count: {{ $salesMonthCount }},  amount: '{{ $nfT($salesMonthAmount) }}' }
    };

    var currentTab = 'today';

    /* ── ApexCharts Area ── */
    var chartEl = document.getElementById('pm-sales-chart');
    var salesChart = null;

    function buildChart(tab) {
        var d = chartData[tab];
        var opts = {
            chart: { type: 'area', height: 230, toolbar: { show: false }, fontFamily: 'inherit', zoom: { enabled: false } },
            series: [{ name: 'تعداد فاکتور', data: d.data }],
            xaxis: { categories: d.labels, labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { style: { fontSize: '11px' }, formatter: function(v){ return parseInt(v); } }, min: 0 },
            colors: ['#7367f0'],
            fill: { type: 'gradient', gradient: { shade: 'light', type: 'vertical', opacityFrom: 0.4, opacityTo: 0.0 } },
            stroke: { curve: 'smooth', width: 2.5 },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
            tooltip: { y: { formatter: function(v){ return v + ' فاکتور'; } } },
        };
        if (salesChart) {
            salesChart.updateOptions(opts, true, true);
        } else {
            if (typeof ApexCharts !== 'undefined' && chartEl) {
                salesChart = new ApexCharts(chartEl, opts);
                salesChart.render();
            }
        }
    }

    function updateStats(tab) {
        var d = chartData[tab];
        var el1 = document.getElementById('sales-count');
        var el2 = document.getElementById('sales-amount');
        if (el1) el1.textContent = d.count.toLocaleString('fa');
        if (el2) el2.textContent = d.amount;
        buildChart(tab);
    }

    document.querySelectorAll('.tab-sales-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-sales-btn').forEach(function(b) {
                b.classList.remove('active');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.add('active');
            this.classList.remove('btn-outline-secondary');
            currentTab = this.dataset.tab;
            updateStats(currentTab);
        });
    });

    if (typeof ApexCharts !== 'undefined') {
        buildChart('today');
    }

})();
</script>

</body>
</html>
