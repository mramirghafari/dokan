<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pishfactor;
use App\Models\Customers;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:drive-delivery,user')->only(['index','create', 'store', 'edit', 'update', 'destroy','pishFactorInfo']);

    }

    public function index()
    {
        $user = \Auth::user();
        // $invoices = Invoice::latest()->get();
        // $subDetails = Detail::all();
        if ($user->isAdmin == 1) {
            $PishFactors = Pishfactor::whereNotNull('driver_id')->get();

        }else {

            $PishFactors = Pishfactor::where('driver_id', $user->id)->get();

        }

        return view('deliveries.delivery_list', compact('PishFactors'));
    }

    public function show($Factor)
    {

        $Factor = Pishfactor::find($Factor);

        return view('deliveries.customer_details', compact('Factor'));
    }

    public function update_by_driver(Request $request, Pishfactor $Factor) {

        dd($request->all());
        dd($Factor);

    }
}
