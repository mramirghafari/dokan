<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Log;
use App\Models\Organization;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:employees,user')->only(['index', 'store', 'edit', 'update', 'destroy', 'trashGet', 'trashPost', 'restore']);
    }

    public function index()
    {
        $employees = $this->scopedEmployeesQuery()->orderByDesc('id')->get();
        $units = Unit::where('isActive', 1)->where('parent_id', null)->get();
        $organizations = $this->scopedOrganizations();

        return view('employees.index', compact('employees', 'units', 'organizations'));
    }

    public function store(Request $request)
    {
        $data = $this->validateEmployee($request);
        $data = $this->withTenantContext($data);
        $data['isActive'] = $request->has('isActive') ? 1 : 0;

        $employee = Employee::create($data);

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'user_id' => Auth::id(),
            'action' => 'create',
            'description' => 'یک پرسنل ایجاد شد' . '-' . $employee->name,
        ]);

        Alert::success('تشکر', 'پرسنل با موفقیت ایجاد شد');
        return redirect()->route('employees.index');
    }

    public function edit(Employee $employee)
    {
        $employees = $this->scopedEmployeesQuery()->orderByDesc('id')->get();
        $units = Unit::where('isActive', 1)->where('parent_id', null)->get();
        $organizations = $this->scopedOrganizations();

        return view('employees.edit', compact('employees', 'units', 'employee', 'organizations'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $this->validateEmployee($request, $employee);
        $data['isActive'] = $request->has('isActive') ? 1 : 0;

        $employee->update($data);

        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => 'یک پرسنل ویرایش شد' . '-' . $employee->name,
        ]);

        Alert::success('تشکر', 'پرسنل با موفقیت ویرایش شد');
        return redirect()->route('employees.index');
    }

    public function getUnit($id)
    {
        $parentUnit_id = Unit::where('organization_id', $id)
            ->where('isActive', 1)->where('parent_id', NULL)->get();
        return response()->json($parentUnit_id);
    }

    public function getChildUnit($id)
    {
        $childUnit_id = Unit::where('parent_id', $id)
            ->where('isActive', 1)->get();
        return response()->json($childUnit_id);
    }

    private function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'parentUnit_id' => ['nullable', 'integer'],
            'childUnit_id' => ['nullable', 'integer'],
            'personalID' => ['nullable', 'string', 'max:50'],
            'national_code' => ['nullable', 'string', 'max:20'],
            'personnel_code' => ['nullable', 'string', 'max:50'],
            'father_name' => ['nullable', 'string', 'max:120'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'employment_type' => ['nullable', 'in:official,contractual,daily,hourly'],
            'hire_date_en' => ['nullable', 'date'],
            'insurance_number' => ['nullable', 'string', 'max:40'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'bank_account' => ['nullable', 'string', 'max:60'],
            'sheba' => ['nullable', 'string', 'max:40'],
            'marital_status' => ['nullable', 'in:single,married'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:30'],
            'military_status' => ['nullable', 'in:done,exempt,not_required'],
            'employment_status' => ['nullable', 'in:active,suspended,terminated'],
        ]);

        if (!empty($validated['hire_date_en'])) {
            $validated['hire_date_fa'] = $this->jalaliDate($validated['hire_date_en']);
        }

        $validated['children_count'] = (int) ($validated['children_count'] ?? 0);
        $validated['employment_status'] = $validated['employment_status'] ?? 'active';

        return $validated;
    }

    private function withTenantContext(array $data): array
    {
        $user = Auth::user();

        if ((int) $user->isGod !== 1) {
            $data['tenant_id'] = $user->tenant_id ?: $user->tenants_id;
        }

        return $data;
    }

    private function scopedEmployeesQuery()
    {
        $user = Auth::user();
        $query = Employee::query();

        if ((int) $user->isGod !== 1) {
            $tenantId = $user->tenant_id ?: $user->tenants_id;
            $query->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            });
        }

        return $query;
    }

    private function scopedOrganizations()
    {
        $user = Auth::user();
        $query = Organization::where('isActive', 1);

        if ((int) $user->isGod !== 1) {
            $tenantId = $user->tenant_id ?: $user->tenants_id;
            $query->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                    ->orWhere('tenants_id', $tenantId)
                    ->orWhereNull('tenant_id');
            });
        }

        return $query->get();
    }

    private function jalaliDate(string $date): string
    {
        try {
            return verta($date)->format('Y/m/d');
        } catch (\Throwable $exception) {
            return $date;
        }
    }
}
