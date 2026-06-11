<?php

namespace App\Http\Controllers;

use App\Models\Log;
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
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => \Auth::user()->id,
            'action' => 'create',
            'description' => 'تامین کننده جدید ایجاد شد' . '-' . $supplier->title
        ]);

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
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => \Auth::user()->id,
            'action' => 'update',
            'description' => 'تامین کننده ویرایش شد' . '-' . $supplier->title
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');

        return redirect()->route('suppliers.edit',compact('supplier','suppliers'));
    }

}
