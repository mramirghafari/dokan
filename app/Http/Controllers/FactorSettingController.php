<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Store;
use App\Models\factorMaker;
use App\Models\Role;
use App\Models\Log;
use App\Services\InvoiceLayoutService;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class FactorSettingController extends Controller
{
    public function __construct(private InvoiceLayoutService $invoiceLayouts)
    {
        $this->middleware('can:factormanager,user')->only(['index', 'store', 'edit', 'update']);
    }

    public function index()
    {
        $User = auth()->user();
        if ($User->isGod == 1) {
            $Organizations = Organization::all();
            $Factors = factorMaker::all();
            $Stores = Store::all();
        } elseif ($User->isAdmin == 1) {
            $Organizations = Organization::forOrganizations($User, 'id')->get();
            $Factors = factorMaker::forOrganizations($User)->get();
            $Stores = Store::forOrganizations($User)->get();
        }

        $layoutProfiles = $this->invoiceLayouts->profileOptions();
        $layoutConfig = config('invoice_layouts.profiles', []);
        $productTypes = $this->invoiceLayouts->productTypeOptions();
        $productTypeProfileMap = $this->invoiceLayouts->productTypeProfileMap();
        $factorMaker = new factorMaker([
            'pr_type' => config('factor_product_types.default', 'non_refrigerated'),
            'business_profile' => 'distribution',
            'column_pr_code' => 1,
            'column_moadian' => 1,
            'column_sub_unit' => 1,
            'column_discount' => 1,
            'column_tax' => 1,
        ]);
        $labelFields = $this->invoiceLayouts->labelFieldsForProfile('distribution');

        return view('FactorManager.index', compact(
            'Organizations',
            'Factors',
            'Stores',
            'layoutProfiles',
            'layoutConfig',
            'productTypes',
            'productTypeProfileMap',
            'factorMaker',
            'labelFields'
        ));
    }

    public function store(Request $request)
    {
        $payload = $this->buildPayload($request);
        $Factor = factorMaker::create($payload);
        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'تنظیمات فاکتور توسط ' . $user->name . ' ایجاد شد' . '-' . $Factor->name,
        ]);

        Alert::success('تشکر', 'قالب فاکتور با موفقیت ایجاد شد');
        return back();
    }

    public function edit(Request $request, $id)
    {
        $User = \Auth::user();
        $factorMaker = factorMaker::find($id);

        if ($User->isGod == 0 && $User->organization_id != null && $factorMaker->organization_id != null) {
            $userOrgans = json_decode($User->organization_id);
            $FactorOrgans = json_decode($factorMaker->organization_id);
            $common = array_intersect($userOrgans, $FactorOrgans);
            if (empty($common)) {
                return redirect(route('FactorManager.index'));
            }
        }

        if ($User->isGod == 1) {
            $Organizations = Organization::all();
            $Stores = Store::all();
        } elseif ($User->isAdmin == 1) {
            $Organizations = Organization::forOrganizations($User, 'id')->get();
            $Stores = Store::forOrganizations($User)->get();
        }

        $layoutProfiles = $this->invoiceLayouts->profileOptions();
        $layoutConfig = config('invoice_layouts.profiles', []);
        $productTypes = $this->invoiceLayouts->productTypeOptions();
        $productTypeProfileMap = $this->invoiceLayouts->productTypeProfileMap();
        $labelFields = $this->invoiceLayouts->labelFieldsForProfile($factorMaker->business_profile ?: 'distribution');

        return view('FactorManager.edit', compact(
            'Organizations',
            'factorMaker',
            'Stores',
            'layoutProfiles',
            'layoutConfig',
            'productTypes',
            'productTypeProfileMap',
            'labelFields'
        ));
    }

    public function update(Request $request, $id)
    {
        $factorMaker = factorMaker::findorFail($id);
        $factorMaker->update($this->buildPayload($request));

        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'تنظیمات فاکتور ویرایش شد' . '-' . $factorMaker->name,
        ]);

        Alert::success('تشکر', 'قالب فاکتور با موفقیت ویرایش شد');
        return redirect()->back();
    }

    private function buildPayload(Request $request): array
    {
        $lineLayout = $this->invoiceLayouts->buildLabelOverridesFromRequest($request->all());

        return [
            'name' => $request->name,
            'type' => $request->type,
            'pr_type' => $this->invoiceLayouts->normalizeProductType($request->pr_type),
            'business_profile' => $request->business_profile ?: 'distribution',
            'line_layout' => $lineLayout,
            'currency_type' => $request->currency_type,
            'seller_name' => $request->seller_name,
            'seller_economic_number' => $request->seller_economic_number,
            'seller_registration_number' => $request->seller_registration_number,
            'seller_id_number' => $request->seller_id_number,
            'seller_address' => $request->seller_address,
            'seller_zip_code' => $request->seller_zip_code,
            'seller_phone' => $request->seller_phone,
            'seller_fax' => $request->seller_fax,
            'buyer_name' => $request->buyer_name,
            'buyer_econimic_code' => $request->buyer_econimic_code,
            'buyer_registration_number' => $request->buyer_registration_number,
            'buyer_address' => $request->buyer_address,
            'buyer_zip_code' => $request->buyer_zip_code,
            'buyer_phone' => $request->buyer_phone,
            'buyer_region_area' => $request->buyer_region_area,
            'buyer_map_code' => $request->buyer_map_code,
            'visitor_display' => $request->visitor_display,
            'visitor_mobile' => $request->visitor_mobile,
            'column_pr_code' => $request->column_pr_code,
            'column_moadian' => $request->column_moadian,
            'column_sub_unit' => $request->column_sub_unit,
            'column_discount' => $request->column_discount,
            'column_tax' => $request->column_tax,
            'organization_id' => json_encode($request->organization_id),
            'store_id' => json_encode($request->store_id),
        ];
    }
}
