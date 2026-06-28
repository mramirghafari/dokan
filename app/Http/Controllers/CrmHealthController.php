<?php

namespace App\Http\Controllers;

use App\Services\CrmHealthAuditService;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class CrmHealthController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:customers,user']);
    }

    public function index(CrmHealthAuditService $service)
    {
        return view('crm.health.index', $service->state(Auth::user()));
    }

    public function snapshot(CrmHealthAuditService $service)
    {
        $snapshot = $service->persist(Auth::user());

        Alert::success('Snapshot ثبت شد', 'امتیاز سلامت CRM: ' . $snapshot->health_score);

        return redirect()->route('crm.health.index');
    }
}
