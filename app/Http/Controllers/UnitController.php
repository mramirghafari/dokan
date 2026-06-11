<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\Unit;
use RealRashid\SweetAlert\Facades\Alert;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:units,user')->only(['index', 'store', 'edit', 'update']);
    }

    public function index()
    {
        $units = Unit::all();
        $parents = Unit::where('parent_id', Null)->get();
        $organizations = Organization::where('isActive', 1)->get();
        return view('units.index', compact('units', 'parents', 'organizations'));
    }

    public function store(Request $request)
    {
        $unit = Unit::create($request->all());
        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'create',
            'description' => 'واحد ایجاد شد' . '-' . $unit->title
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ایجاد شد');
        return back();
    }

    public function edit(Unit $unit)
    {
        $units = Unit::all();
        $parents = Unit::where('parent_id', Null)->where('id', '!=', $unit->id)->get();
        $organizations = Organization::where('isActive', 1)->get();
        return view('units.edit', compact('unit', 'units', 'parents', 'organizations'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->isActive == "on" ? $request->isActive = 1 : $request->isActive = 0;
        $organizations = Organization::where('isActive', 1)->get();

        $unit->update([
            'title' => $request->title,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'organization_id' => $request->organization_id,
            'isActive' => $request->isActive
        ]);
        $units = Unit::all();
        $parents = Unit::where('parent_id', Null)->get();
        $user = \Auth::user();
        Log::create([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $user->id,
            'action' => 'update',
            'description' => 'واحد ویرایش شد' . '-' . $unit->title
        ]);

        Alert::success('تشکر', 'رکورد با موفقیت ویرایش شد');
        return redirect()->route('units.edit', compact('unit', 'units', 'parents', 'organizations'));
    }

    public function parents($id)
    {
        $parent_id = Unit::where('organization_id', $id)
            ->where('parent_id', Null)->where('isActive', 1)->get();
        return response()->json($parent_id);
    }
}
