<?php

namespace App\Http\Controllers;

use App\Services\CrmDashboardService;
use Illuminate\Support\Facades\Auth;

class CrmDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user');
    }

    public function index(CrmDashboardService $dashboardService)
    {
        return view('crm.dashboard.index', [
            'dashboard' => $dashboardService->forUser(Auth::user()),
        ]);
    }
}
