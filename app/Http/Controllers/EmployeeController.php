<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Log;
use App\Models\Organization;
use App\Models\Unit;
use RealRashid\SweetAlert\Facades\Alert;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:employees,user')->only(['index', 'store', 'edit', 'update', 'destroy', 'trashGet', 'trashPost', 'restore']);
    }

    public function index()
    {
        $employees = Employee::all();
        $units = Unit::where('isActive', 1)->where('parent_id',null)->get();
        $organizations = Organization::where('isActive', 1)->get();
        return view('employees.index', compact('employees', 'units', 'organizations'));
    }

    public function store(Request $request)
    {
        $employee = Employee::create($request->all());
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => \Auth::user()->id,
            'action' => 'create',
            'description' => 'یک پرسنل ایجاد شد' . '-' . $employee->name
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');
        return back();
    }

    public function edit(Employee $employee)
    {
        $employees = Employee::all();
        $units = Unit::where('isActive', 1)->where('parent_id',null)->get();
        $organizations = Organization::where('isActive', 1)->get();

        return view('employees.edit', compact('employees', 'units', 'employee', 'organizations'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->isActive == "on" ? $request->isActive = 1 : $request->isActive = 0;
        $employee->update([
            'name' => $request->name,
            'parentUnit_id' => $request->parentUnit_id,
            'childUnit_id' => $request->childUnit_id,
            'personalID' => $request->personalID,
            'isActive' => $request->isActive
        ]);
        $employees = Employee::all();
        $units = Unit::where('isActive', 1)->get();
        $organizations = Organization::where('isActive', 1)->get();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => \Auth::user()->id,
            'action' => 'create',
            'description' => 'یک پرسنل ویرایش شد' . '-' . $employee->name
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return view('employees.edit', compact('employee', 'employees', 'units', 'organizations'));
    }

    public function getUnit($id)
    {
        $parentUnit_id = Unit::where('organization_id', $id)
            ->where('isActive', 1)->where('parent_id',NULL)->get();
        return response()->json($parentUnit_id);
    }

    public function getChildUnit($id)
    {
        $childUnit_id = Unit::where('parent_id', $id)
            ->where('isActive', 1)->get();
        return response()->json($childUnit_id);
    }


}
