<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\Supplier;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:suppliers,user')->only(['index', 'store', 'edit', 'update', 'destroy', 'trashGet', 'trashPost', 'restore']);
    }

    public function index()
    {
        $suppliers = Supplier::all();
        return view('suppliers.index',compact('suppliers'));
    }

    public function store(Request $request)
    {
        $supplier = Supplier::create($request->all());
        ActivityLogService::safeLogModel('create', 'تامین کننده جدید ایجاد شد' . '-' . $supplier->title, $supplier, ['section' => 'procurement', 'event_key' => 'supplier.create']);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');

        return back();
    }

    public function edit(Supplier $supplier)
    {
        $suppliers = supplier::all();
        return view('suppliers.edit',compact('supplier','suppliers'));
    }

    public function update(Request $request, supplier $supplier)
    {
        $supplier->update([
            'title' => $request->title,
            'description' => $request->description,
            'name' => $request->name,
            'phone' => $request->phone,
            'mobile' => $request->mobile,
            'address' => $request->address
        ]);
        $suppliers = supplier::all();
        ActivityLogService::safeLogModel('update', 'تامین کننده ویرایش شد' . '-' . $supplier->title, $supplier, ['section' => 'procurement', 'event_key' => 'supplier.update']);

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');

        return redirect()->route('suppliers.edit',compact('supplier','suppliers'));
    }

}
