<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\Accounts;
use App\Services\StandardChartImporter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->isGod == 1) {
            $Accounts = Accounts::all();
        } else {
            $Accounts = Accounts::where('tenants_id', $user->tenants_id)->get();
            $MainAccounts = Accounts::where('tenants_id', $user->tenants_id)->where('parent_id', 0)->get();
        }

        return view('account.index', compact('Accounts', 'MainAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * ساخت خودکار سرفصل‌های استاندارد (الگوی سپیدار) برای پنل جاری.
     */
    public function importStandard(StandardChartImporter $importer)
    {
        $user = Auth::user();
        $result = $importer->import();

        ActivityLogService::safeLog('create', 'سرفصل‌های استاندارد (الگوی سپیدار) ایجاد شد — ' . $result['created'], null, [
            'section' => 'accounting',
            'event_key' => 'account.import_standard',
            'payload' => $result,
        ]);

        if ($result['created'] > 0) {
            Alert::success('انجام شد', $result['created'] . ' سرفصل استاندارد ساخته شد' . ($result['skipped'] ? ' و ' . $result['skipped'] . ' مورد تکراری رد شد.' : '.'));
        } else {
            Alert::info('بدون تغییر', 'همهٔ سرفصل‌های استاندارد از قبل وجود داشتند.');
        }

        return redirect()->back();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());
        $user = Auth::user();
        $request['tenants_id'] = $user->tenants_id;
        $request['created_by'] = $user->id;
        $Account = Accounts::create($request->all());

        ActivityLogService::safeLogModel('create', 'حساب جدید ایجاد شد' . '-' . $Account->name, $Account, ['section' => 'accounting', 'event_key' => 'account.created']);

        Alert::success('تشکر', 'حساب جدید با موفقیت ایجاد شد');
        return redirect()->back();
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
        $Account = Accounts::find($id);
        $user = Auth::user();
        if ($user->isGod == 1) {
            $Accounts = Accounts::all();
        } else {
            $Accounts = Accounts::where('tenants_id', $user->tenants_id)->get();
            $MainAccounts = Accounts::where('tenants_id', $user->tenants_id)->where('parent_id', 0)->get();
        }

        return view('account.edit', compact('Account', 'Accounts', 'MainAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //dd($request->all());
        $Account = Accounts::find($id);
        $Account->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'level' => $request->level,
            'parent_id' => $request->parent_id,
            'type' => $request->type,
            'account_category' => $request->account_category,
            'asset_class' => in_array($request->account_category, ['asset', 'liability'], true) ? $request->asset_class : null,
            'asset_type' => $request->asset_type,
            'detail_type' => $request->detail_type,
            'is_control' => $request->boolean('is_control'),
            'is_system' => $request->boolean('is_system'),
            'cost_center_required' => $request->boolean('cost_center_required'),
            'floating_detail_required' => $request->boolean('floating_detail_required'),
            'nature' => $request->nature,
            'opening_balance' => $request->opening_balance,
            'account_number' => $request->account_number,
            'card_number' => $request->card_number,
            'iban' => $request->iban,
            'branch' => $request->branch,
        ]);

        ActivityLogService::safeLogModel('update', 'حساب ویرایش شد' . '-' . $Account->name, $Account, ['section' => 'accounting', 'event_key' => 'account.updated']);

        Alert::success('تشکر', 'ویرایش حساب با موفقیت انجام شد.');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
