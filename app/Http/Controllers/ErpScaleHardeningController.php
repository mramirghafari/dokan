<?php

namespace App\Http\Controllers;

use App\Services\ErpScaleHardeningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class ErpScaleHardeningController extends Controller
{
    public function index(ErpScaleHardeningService $service)
    {
        return view('erp.scale_hardening.index', $service->state(Auth::user()));
    }

    public function snapshot(ErpScaleHardeningService $service)
    {
        $snapshot = $service->persist(Auth::user());

        Alert::success('Snapshot ثبت شد', 'امتیاز آمادگی مقیاس: ' . $snapshot->readiness_score);

        return redirect()->route('erp.scale-hardening.index');
    }

    public function lookup(Request $request, ErpScaleHardeningService $service)
    {
        $data = $request->validate([
            'entity' => ['required', 'string', 'max:40'],
            'q' => ['nullable', 'string', 'max:120'],
            'limit' => ['nullable', 'integer', 'min:5', 'max:50'],
            'id' => ['nullable', 'integer', 'min:1'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'min:1'],
            'filters' => ['nullable', 'array'],
            'filters.is_active' => ['nullable', 'integer', 'in:0,1'],
            'filters.is_material' => ['nullable', 'integer', 'in:0,1'],
        ]);

        $user = Auth::user();
        $filters = $data['filters'] ?? [];

        if (!empty($data['id'])) {
            return response()->json([
                'results' => $service->resolveByIds($user, $data['entity'], [(int) $data['id']], $filters),
            ]);
        }

        if (!empty($data['ids'])) {
            return response()->json([
                'results' => $service->resolveByIds($user, $data['entity'], $data['ids'], $filters),
            ]);
        }

        return response()->json([
            'results' => $service->remoteLookup(
                $user,
                $data['entity'],
                $data['q'] ?? '',
                (int) ($data['limit'] ?? 20),
                $filters
            ),
        ]);
    }
}
