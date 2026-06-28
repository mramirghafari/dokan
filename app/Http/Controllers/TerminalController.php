<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\Accounts;
use App\Models\Organization;
use App\Models\PaymentTerminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class TerminalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->isGod == 1) {
            $Terminals = PaymentTerminal::all();
            $Accounts = Accounts::where('level', 3)->where('isActive', 1)->get();
            $Organizations = Organization::all();
        } else {
            $Terminals = PaymentTerminal::where('tenants_id', $user->tenants_id)->get();
            // Level 3 = Tafsili
            $Accounts = Accounts::where('level', 3)->where('tenants_id', $user->tenants_id)->where('isActive', 1)->get();
            $Organizations = Organization::forOrganizations($user, 'id')->get();
        }


        return view('terminals.index', compact('Terminals', 'Accounts', 'Organizations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $user = Auth::user();
        $request['tenants_id'] = $user->tenants_id;
        $request['tenant_id'] = $user->tenant_id ?: $user->tenants_id;
        $request['created_by'] = $user->id;
        $request['organization_id'] = json_encode($request->organization_id);
        $request['terminal_kind'] = $this->terminalKind($request->terminal_type);
        $request['settlement_account_id'] = $request->account_id;
        $request['terminal_status'] = 'active';
        $request['settlement_cycle'] = $request->settlement_cycle ?: 'daily';

        //dd($request->all());
        $Terminal = PaymentTerminal::create($request->all());

        ActivityLogService::safeLogModel('create', 'پایانه جدید ایجاد شد' . '-' . $Terminal->name, $Terminal, ['section' => 'accounting', 'event_key' => 'terminal.created']);

        Alert::success('تشکر', 'پایانه جدید با موفقیت ایجاد شد');
        return redirect()->back();
    }

    private function terminalKind($legacyType): string
    {
        return match ((int) $legacyType) {
            2 => 'gateway',
            3 => 'ussd',
            default => 'pos',
        };
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
