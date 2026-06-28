<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\Product;
use App\Models\Store;
use App\Models\WarehouseLocation;
use App\Models\Depot;
use App\Models\InventoryBalance;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use Hekmatinasser\Verta\Verta;
use App\Services\AccountingPostingService;
use App\Services\InventoryLedgerService;
use App\Services\ReceiptAiService;
use App\Services\TenantSettings;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_warehouse_management')) {
                Alert::warning('غیرفعال', 'مدیریت انبار و موجودی برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        });
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_multi_warehouse')) {
                Alert::warning('غیرفعال', 'انتقال بین انبارها برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        })->only(['storeTransfer']);
    }

    public function index()
    {
        $user = Auth::user();
        if ($user->isGod == 1) {

            $Stores = Store::all();
        } else {
            $userOrgs = is_array(json_decode($user->organization_id))
                ? json_decode($user->organization_id)
                : [intval($user->organization_id)];

            // تبدیل به آرایه‌ای از هم int هم string
            $searchValues = [];
            foreach ($userOrgs as $orgId) {
                $searchValues[] = intval($orgId);
                $searchValues[] = strval($orgId);
            }

            $Stores = Store::where(function ($q) use ($searchValues) {
                foreach ($searchValues as $orgId) {
                    $q->orWhereJsonContains('organization_id', $orgId)  // استورها JSON باشن (int یا string)
                        ->orWhere('organization_id', $orgId);             // استورها عدد باشن
                }
            })->get();
        }


        return view('stocks.receipts', compact('Stores'));
    }

    public function storeReceipts(Request $request, Store $store)
    {
        $user = Auth::user();

        $Receipts = Receipt::query()
            ->select([
                'id',
                'user_id',
                'type',
                'store_id',
                'number',
                'date_fa',
                'date_en',
                'sender',
                'scale_ticket_number',
                'vehicle_plate',
                'net_weight',
                'document_status',
                'return_source_receipt_id',
                'created_at',
            ])
            ->with(['store:id,title', 'user:id,name'])
            ->where('store_id', $store->id)
            ->when(!($user->isGod == 1 || $user->isAdmin == 1), fn($query) => $query->where('user_id', $user->id))
            ->when($request->filled('document_status'), fn($query) => $query->where('document_status', $request->document_status))
            ->when($request->filled('type'), fn($query) => $query->where('type', $request->type))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim((string) $request->q);
                $query->where(function ($query) use ($term) {
                    $query->where('number', $term)
                        ->orWhere('sender', 'like', '%' . $term . '%')
                        ->orWhere('scale_ticket_number', 'like', '%' . $term . '%')
                        ->orWhere('vehicle_plate', 'like', '%' . $term . '%');
                });
            })
            ->when($request->filled('from_date'), fn($query) => $query->whereDate('date_en', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn($query) => $query->whereDate('date_en', '<=', $request->to_date))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();


        return view('stocks.storeReceipts', compact('Receipts', 'store'));
    }

    public function storeReceiptShow(Store $store, Receipt $receipt)
    {
        $user = Auth::user();

        if ($user->isGod == 1) {
            $Stores = Store::all();
            $WarehouseLocations = WarehouseLocation::where('is_active', 1)->with('store')->orderBy('store_id')->orderBy('sort_order')->orderBy('code')->get();
        } else {
            $Stores = Store::forOrganizations($user)->where('isActive', 1)->get();
            $WarehouseLocations = WarehouseLocation::forOrganizations($user)->where('is_active', 1)->with('store')->orderBy('store_id')->orderBy('sort_order')->orderBy('code')->get();
        }

        $Depots = Depot::where('receipt_id', $receipt->id)->with(['product', 'store', 'warehouseLocation'])->get();
        $ReturnableDepots = $this->returnableDepotRows($Depots);
        $Products = Product::whereJsonContains('store_id', ["$store->id"])->get();
        $WarehouseLocationMode = TenantSettings::get('warehouse_location_mode', null, 'optional_locations');


        return view('stocks.importPageEdit', compact('Stores', 'store', 'receipt', 'Depots', 'ReturnableDepots', 'Products', 'WarehouseLocations', 'WarehouseLocationMode'));
    }



    public function store(Request $request)
    {

        //dd($request->all());
        $user = Auth::user();

        $request['user_id'] = $user->id;
        $request->merge($this->receiptScopePayload($request->store_id, $user));
        $request->merge($this->approvedDocumentPayload($user));
        $request->merge($this->weighbridgePayload($request));
        $Product = Product::find($request->pr_id);

        if ($request->date_fa != '') {
            $arrayDate = explode(" ", $request->get('date_fa'));
            $fromFaHustDate = $arrayDate[0];
            $jalaliFrom = explode("/", $fromFaHustDate);
            $miladiFrom = Verta::jalaliToGregorian($jalaliFrom[0], $jalaliFrom[1], $jalaliFrom[2]);
            $ymF = $miladiFrom[0];
            if (strlen($miladiFrom[1]) == 1) {
                $mmF = "0" . $miladiFrom[1];
            } else {
                $mmF = $miladiFrom[1];
            };
            if (strlen($miladiFrom[2]) == 1) {
                $dmF = "0" . $miladiFrom[2];
            } else {
                $dmF = $miladiFrom[2];
            };

            $dateEn = "$ymF-$mmF-$dmF 00:00:00";
        } else {
            $dateEn = null;
        }

        $request['date_en'] = $dateEn;
        $Receipt = Receipt::create($request->all());

        if ($request->has('pr_id')) {
            $pr_ids = $request->get('pr_id');
            $units = $request->get('unit');
            $sub_units = $request->get('sub_unit');
            $warehouseLocationIds = $this->normalizedWarehouseLocationIds($request, 'warehouse_location_id', count($pr_ids), 'مکان/قفسه');

            $x = 0;
            foreach ($pr_ids as $pr_id) {
                $Depot = new Depot();
                $Depot->pr_id = $pr_id;
                $Depot->receipt_id = $Receipt->id;
                $Depot->tenant_id = $Receipt->tenant_id;
                $Depot->entity = $units[$x];
                $Depot->entity_sub_unit = $sub_units[$x] ?? 0;
                $Depot->store_id = $request->store_id;
                $Depot->warehouse_location_id = $warehouseLocationIds[$x] ?? 0;
                $Depot->fill($this->tracePayload($request, $x));
                $Depot->status = 1;
                $Depot->save();
                $x++;
            }
        }

        $this->syncApprovedReceiptEffects($Receipt, $user);

        ActivityLogService::safeLog('create', "ثبت رسید جدید برای انبار", null, ['section' => 'system', 'event_key' => 'system.create']);

        Alert::success('تشکر', "رسید جدید با موفقیت ثبت گردید.");
        return back();
    }

    public function update(Request $request, Receipt $receipt)
    {
        $user = auth()->user();

        if ($receipt->document_status === 'canceled') {
            Alert::warning('غیرقابل ویرایش', 'سند ابطال شده قابل ویرایش نیست.');
            return redirect()->back();
        }

        $scopePayload = $this->receiptScopePayload($request->store_id, $user);
        $weighbridgePayload = $this->weighbridgePayload($request);

        /* ------------------------------------
           1. تاریخ
        ------------------------------------ */
        if ($request->date_fa != '') {
            $fromDate = str_replace("/", "-", $request->get('date_fa'));
            $jalaliFrom = explode("-", $fromDate);
            $miladiFrom = Verta::jalaliToGregorian(
                $jalaliFrom[0],
                $jalaliFrom[1],
                $jalaliFrom[2]
            );

            $date_en = sprintf(
                '%04d-%02d-%02d 00:00:00',
                $miladiFrom[0],
                $miladiFrom[1],
                $miladiFrom[2]
            );
        }

        /* ------------------------------------
           2. بروزرسانی رسید
        ------------------------------------ */
        $receipt->update([
            'type'      => $request->type,
            'store_id'  => $request->store_id,
            'tenant_id' => $scopePayload['tenant_id'],
            'organization_id' => $scopePayload['organization_id'],
            'input_id'  => $request->input_id,
            'date_fa'   => $request->date_fa,
            'date_en'   => $date_en ?? $receipt->date_en,
            'sender'    => $request->sender,
            'moeen'     => $request->moeen,
            'driver'    => $request->driver,
            'scale_ticket_number' => $weighbridgePayload['scale_ticket_number'],
            'vehicle_plate' => $weighbridgePayload['vehicle_plate'],
            'waybill_number' => $weighbridgePayload['waybill_number'],
            'gross_weight' => $weighbridgePayload['gross_weight'],
            'tare_weight' => $weighbridgePayload['tare_weight'],
            'net_weight' => $weighbridgePayload['net_weight'],
            'weighing_notes' => $weighbridgePayload['weighing_notes'],
            'tozihat'   => $request->tozihat,
            'updated_at' => now()
        ]);

        if (isset($date_en) && $receipt->created_at != $date_en) {
            $receipt->update(['created_at' => $date_en]);
        }

        /* ------------------------------------
           3. ورودی‌های جدول
        ------------------------------------ */
        $prIds     = $request->get('pr_id', []);
        $units     = $request->get('unit', []);
        $subUnits  = $request->get('sub_unit', []);
        $warehouseLocationIds = $this->normalizedWarehouseLocationIds($request, 'warehouse_location_id', count($prIds), 'مکان/قفسه');

        /* ------------------------------------
           4. حذف آیتم‌هایی که دیگر در جدول نیستند
        ------------------------------------ */
        Depot::where('receipt_id', $receipt->id)
            ->where('store_id', $receipt->store_id)
            ->whereNotIn('pr_id', array_filter($prIds))
            ->delete();

        /* ------------------------------------
           5. insert / update با حفظ ترتیب
        ------------------------------------ */
        foreach ($prIds as $index => $prId) {

            if (!$prId) continue;

            Depot::updateOrCreate(
                [
                    'receipt_id' => $receipt->id,
                    'store_id'   => $receipt->store_id,
                    'pr_id'      => $prId,
                    'batch_no' => $this->traceValue($request, 'batch_no', $index),
                    'lot_no' => $this->traceValue($request, 'lot_no', $index),
                    'serial_no' => $this->traceValue($request, 'serial_no', $index),
                ],
                [
                    'tenant_id' => $receipt->tenant_id,
                    'entity' => $units[$index] ?? 0,
                    'entity_sub_unit' => $subUnits[$index] ?? 0,
                    'warehouse_location_id' => $warehouseLocationIds[$index] ?? 0,
                    ...$this->tracePayload($request, $index),
                    'status' => 1,
                    'order_index' => $index // ⭐ ترتیب
                ]
            );
        }

        $this->syncApprovedReceiptEffects($receipt, $user);

        /* ------------------------------------
           6. لاگ
        ------------------------------------ */
        ActivityLogService::safeLogModel('update', "سند انبار توسط {$user->name} ویرایش شد.", $receipt, ['section' => 'warehouse', 'event_key' => 'receipt.updated']);

        Alert::success('انجام شد', 'سند انبار با موفقیت ویرایش شد');

        return redirect()->route(
            'stocks.storeReceiptShow',
            ['store' => $receipt->store_id, 'receipt' => $receipt->id]
        );
    }


    public function storeTransfer(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();

        $request['user_id'] = $user->id;
        $request->merge($this->receiptScopePayload($request->store_id, $user));
        $request->merge($this->approvedDocumentPayload($user));
        $request->merge($this->weighbridgePayload($request));
        // Receipt type = 6 Store Transfer
        $Receipt = Receipt::create($request->all());

        $store_id  = $request->get('store_id');
        $to_store_id  = $request->get('to_store_id');
        $pr_ids = $request->get('pr_id');
        $units = $request->get('unit');
        $sub_units = $request->get('sub_unit');
        $fromLocationIds = $this->normalizedWarehouseLocationIds($request, 'from_warehouse_location_id', count($pr_ids), 'مکان مبدا');
        $toLocationIds = $this->normalizedWarehouseLocationIds($request, 'to_warehouse_location_id', count($pr_ids), 'مکان مقصد');

        if ($request->has('pr_id')) {
            $x = 0;
            foreach ($pr_ids as $pr_id) {
                // Depot for Export From Store
                $Depot = new Depot();
                $Depot->pr_id = $pr_id;
                $Depot->receipt_id = $Receipt->id;
                $Depot->tenant_id = $Receipt->tenant_id;
                $Depot->entity = $units[$x];
                $Depot->entity_sub_unit = $sub_units[$x] ?? 0;
                $Depot->store_id = $store_id;
                $Depot->warehouse_location_id = $fromLocationIds[$x] ?? 0;
                $Depot->fill($this->tracePayload($request, $x));
                $Depot->status = 0;
                $Depot->save();

                $Depot = new Depot();
                $Depot->pr_id = $pr_id;
                $Depot->receipt_id = $Receipt->id;
                $Depot->tenant_id = $Receipt->tenant_id;
                $Depot->entity = $units[$x];
                $Depot->entity_sub_unit = $sub_units[$x] ?? 0;
                $Depot->store_id = $to_store_id;
                $Depot->warehouse_location_id = $toLocationIds[$x] ?? 0;
                $Depot->fill($this->tracePayload($request, $x));
                $Depot->status = 1;
                $Depot->save();
                $x++;
            }
        }

        $this->syncApprovedReceiptEffects($Receipt, $user);

        ActivityLogService::safeLog('create', "ثبت رسید جدید برای انبار", null, ['section' => 'system', 'event_key' => 'system.create']);

        Alert::success('ثبت انتقال', "انتقال بین انبار با موفقیت انجام شد.");
        return back();
    }


    public function importReceiptAi(Request $request, ReceiptAiService $service)
    {
        try {
            /* ---------- VALIDATION ---------- */
            $request->validate([
                'receipt_photo' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:8192',
                ],
            ]);
            /* ---------- ABSOLUTE PATH ---------- */
            $uploadDir = '/home/darami/public_html/newdokan/storage/receipts_ai';

            if (!is_dir($uploadDir)) {
                throw new \Exception('Upload directory does not exist');
            }

            if (!is_writable($uploadDir)) {
                throw new \Exception('Upload directory is not writable');
            }

            /* ---------- SAFE FILENAME ---------- */
            $file = $request->file('receipt_photo');

            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

            /* ---------- MOVE FILE ---------- */
            $file->move($uploadDir, $filename);

            /* ---------- PUBLIC URL ---------- */
            $imageUrl = url("/storage/receipts_ai/{$filename}");

            /* ---------- PROCESS AI ---------- */
            $result = app(ReceiptAiService::class)
                ->processImage($imageUrl);

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function deleteReceipt(Request $request, Receipt $receipt)
    {

        $user = Auth::user();
        $Store = Store::find($receipt->store_id);
        $Storetitle = $Store->title;
        $postingService = app(AccountingPostingService::class);

        if ($postingService->hasPermanentReceiptInventoryVoucher($receipt)) {
            Alert::warning('غیرقابل حذف', 'برای این رسید سند حسابداری دائمی ثبت شده است. ابتدا سند مالی برگشت/اصلاحیه ثبت شود.');
            return redirect()->back();
        }

        app(InventoryLedgerService::class)->removeReceiptMovements($receipt);
        $postingService->removeReceiptInventoryVoucher($receipt);
        $Depots = Depot::where('receipt_id', $receipt->id)->delete();
        $Num = $receipt->number;
        $receipt->delete();

        ActivityLogService::safeLog('create', "حذف رسید شماره $Num برای انبار $Storetitle    برای انبار", null, ['section' => 'system', 'event_key' => 'system.create']);

        Alert::success('حذف موفق', "رسید مورد نظر حذف شذ.");
        return redirect()->back();
    }

    public function approveReceipt(Request $request, Receipt $receipt)
    {
        $user = Auth::user();

        if ($receipt->document_status === 'canceled') {
            Alert::warning('غیرقابل تایید', 'سند ابطال شده قابل تایید مجدد نیست.');
            return redirect()->back();
        }

        $receipt->update($this->approvedDocumentPayload($user));
        $this->syncApprovedReceiptEffects($receipt, $user);

        ActivityLogService::safeLogModel('approve', "سند انبار شماره " . ($receipt->number ?: $receipt->id) . " تایید شد.", $receipt, ['section' => 'warehouse', 'event_key' => 'receipt.approve']);

        Alert::success('تایید شد', 'سند انبار تایید و در دفتر گردش کالا اعمال شد.');
        return redirect()->back();
    }

    public function cancelReceipt(Request $request, Receipt $receipt)
    {
        $user = Auth::user();
        $postingService = app(AccountingPostingService::class);

        if ($postingService->hasPermanentReceiptInventoryVoucher($receipt)) {
            Alert::warning('غیرقابل ابطال', 'برای این سند انبار سند حسابداری دائمی ثبت شده است. ابتدا سند مالی برگشت/اصلاحیه ثبت شود.');
            return redirect()->back();
        }

        app(InventoryLedgerService::class)->removeReceiptMovements($receipt);
        $postingService->removeReceiptInventoryVoucher($receipt);

        $receipt->update([
            'document_status' => 'canceled',
            'canceled_at' => now(),
            'canceled_by' => $user->id,
            'cancellation_reason' => $request->input('cancellation_reason'),
            'updated_at' => now(),
        ]);

        ActivityLogService::safeLogModel('cancel', "سند انبار شماره " . ($receipt->number ?: $receipt->id) . " ابطال شد.", $receipt, ['section' => 'warehouse', 'event_key' => 'receipt.cancel']);

        Alert::success('ابطال شد', 'سند انبار ابطال شد و اثر آن از موجودی حذف شد.');
        return redirect()->back();
    }

    public function returnReceipt(Request $request, Receipt $receipt)
    {
        $user = Auth::user();

        if ($receipt->document_status !== 'approved') {
            Alert::warning('غیرقابل برگشت', 'فقط سند انبار تایید شده قابل برگشت است.');
            return redirect()->back();
        }

        if ($receipt->return_source_receipt_id) {
            Alert::warning('غیرقابل برگشت', 'برای سند برگشت، برگشت زنجیره ای ثبت نمی شود. سند اصلاحی جدید ثبت کنید.');
            return redirect()->back();
        }

        $request->validate([
            'return_date' => ['nullable', 'date'],
            'return_reason' => ['required', 'string', 'max:1000'],
            'source_depot_id' => ['required', 'array'],
            'source_depot_id.*' => ['nullable', 'integer'],
            'return_quantity' => ['required', 'array'],
            'return_quantity.*' => ['nullable', 'numeric', 'min:0'],
            'return_sub_unit' => ['nullable', 'array'],
            'return_sub_unit.*' => ['nullable', 'numeric', 'min:0'],
            'return_weight' => ['nullable', 'array'],
            'return_weight.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $sourceDepotIds = array_filter($request->get('source_depot_id', []));
        $sourceDepots = Depot::query()
            ->with('product')
            ->where('receipt_id', $receipt->id)
            ->whereIn('id', $sourceDepotIds)
            ->get()
            ->keyBy('id');

        $returnLines = [];
        $quantities = $request->get('return_quantity', []);
        $subUnits = $request->get('return_sub_unit', []);
        $weights = $request->get('return_weight', []);

        foreach ($sourceDepotIds as $index => $sourceDepotId) {
            $sourceDepot = $sourceDepots->get((int) $sourceDepotId);
            $quantity = $this->decimalInput($quantities[$index] ?? null) ?: 0.0;

            if (!$sourceDepot || $quantity <= 0) {
                continue;
            }

            $remainingQuantity = $this->remainingReturnQuantity($sourceDepot);
            if ($quantity > $remainingQuantity + 0.0001) {
                throw ValidationException::withMessages([
                    'return_quantity.' . $index => 'مقدار برگشت بیشتر از مانده قابل برگشت کالا است.',
                ]);
            }

            $returnStatus = (int) $sourceDepot->status === 1 ? 0 : 1;
            if ($returnStatus === 0 && $this->availableStockForDepot($sourceDepot) + 0.0001 < $quantity) {
                throw ValidationException::withMessages([
                    'return_quantity.' . $index => 'موجودی فعلی برای برگشت این ردیف کافی نیست.',
                ]);
            }

            $returnLines[] = [
                'source_depot' => $sourceDepot,
                'quantity' => $quantity,
                'sub_unit' => $this->returnSubUnit($sourceDepot, $quantity, $subUnits[$index] ?? null),
                'weight' => $this->returnWeight($sourceDepot, $quantity, $weights[$index] ?? null),
                'status' => $returnStatus,
            ];
        }

        if (!$returnLines) {
            throw ValidationException::withMessages([
                'return_quantity' => 'حداقل یک مقدار برگشت معتبر وارد کنید.',
            ]);
        }

        $returnDate = $request->filled('return_date') ? Carbon::parse($request->return_date) : now();
        $returnType = collect($returnLines)->pluck('status')->unique()->count() > 1
            ? 12
            : (collect($returnLines)->first()['status'] === 0 ? 10 : 11);

        $returnReceipt = DB::transaction(function () use ($receipt, $request, $user, $returnDate, $returnType, $returnLines) {
            $returnReceipt = Receipt::create(array_merge([
                'user_id' => $user->id,
                'tenant_id' => $receipt->tenant_id,
                'organization_id' => $receipt->organization_id,
                'type' => $returnType,
                'store_id' => $returnLines[0]['source_depot']->store_id,
                'to_store_id' => $receipt->to_store_id,
                'number' => $this->nextReceiptNumber(),
                'date_fa' => verta($returnDate)->format('Y/m/d'),
                'date_en' => $returnDate->toDateTimeString(),
                'sender' => $receipt->sender,
                'moeen' => $receipt->moeen,
                'driver' => $receipt->driver,
                'tozihat' => 'برگشت از سند انبار شماره ' . ($receipt->number ?: $receipt->id) . ' - ' . $request->return_reason,
                'return_source_receipt_id' => $receipt->id,
                'return_reason' => $request->return_reason,
            ], $this->approvedDocumentPayload($user)));

            foreach ($returnLines as $line) {
                $sourceDepot = $line['source_depot'];
                $returnDepot = new Depot();
                $returnDepot->pr_id = $sourceDepot->pr_id;
                $returnDepot->receipt_id = $returnReceipt->id;
                $returnDepot->source_depot_id = $sourceDepot->id;
                $returnDepot->tenant_id = $sourceDepot->tenant_id ?: $returnReceipt->tenant_id;
                $returnDepot->entity = $line['quantity'];
                $returnDepot->entity_sub_unit = $line['sub_unit'];
                $returnDepot->store_id = $sourceDepot->store_id;
                $returnDepot->warehouse_location_id = $sourceDepot->warehouse_location_id ?: 0;
                $returnDepot->batch_no = $sourceDepot->batch_no;
                $returnDepot->lot_no = $sourceDepot->lot_no;
                $returnDepot->serial_no = $sourceDepot->serial_no;
                $returnDepot->manufactured_at = $sourceDepot->manufactured_at;
                $returnDepot->expiry_date = $sourceDepot->expiry_date;
                $returnDepot->color = $sourceDepot->color;
                $returnDepot->size = $sourceDepot->size;
                $returnDepot->quality_grade = $sourceDepot->quality_grade;
                $returnDepot->weight = $line['weight'];
                $returnDepot->tracking_notes = $sourceDepot->tracking_notes;
                $returnDepot->return_reason = $request->return_reason;
                $returnDepot->price = $sourceDepot->price;
                $returnDepot->status = $line['status'];
                $returnDepot->save();
            }

            $this->syncApprovedReceiptEffects($returnReceipt, $user);

            return $returnReceipt;
        });

        ActivityLogService::safeLogModel('create', 'سند برگشت انبار شماره ' . ($returnReceipt->number ?: $returnReceipt->id) . ' از سند ' . ($receipt->number ?: $receipt->id) . ' ثبت شد.', $returnReceipt, ['section' => 'warehouse', 'event_key' => 'returnreceipt.create']);

        Alert::success('برگشت ثبت شد', 'سند برگشت انبار ثبت و در موجودی و حسابداری اعمال شد.');

        return redirect()->route('stocks.storeReceiptShow', [$returnReceipt->store_id, $returnReceipt->id]);
    }

    private function receiptScopePayload($storeId, $user): array
    {
        $store = Store::find($storeId);
        $tenantId = $store ? ($store->tenant_id ?: $store->tenants_id) : null;

        return [
            'tenant_id' => $tenantId ?: ($user->tenant_id ?: $user->tenants_id),
            'organization_id' => $store ? $this->primaryOrganizationId($store->organization_id) : null,
        ];
    }

    private function primaryOrganizationId($organizationId): ?int
    {
        $decoded = json_decode((string) $organizationId, true);

        if (is_array($decoded)) {
            return (int) ($decoded[0] ?? 0) ?: null;
        }

        return $organizationId ? (int) $organizationId : null;
    }

    private function approvedDocumentPayload($user): array
    {
        return [
            'document_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $user->id,
            'canceled_at' => null,
            'canceled_by' => null,
            'cancellation_reason' => null,
        ];
    }

    private function syncApprovedReceiptEffects(Receipt $receipt, $user): void
    {
        $receipt = $receipt->fresh(['depots.product']) ?: $receipt;

        if ($receipt->document_status === 'approved') {
            app(InventoryLedgerService::class)->replaceReceiptMovements($receipt, $receipt->depots()->get(), $user->id);
            app(AccountingPostingService::class)->postReceiptInventoryVoucher($receipt, $user);
            return;
        }

        app(InventoryLedgerService::class)->removeReceiptMovements($receipt);
        app(AccountingPostingService::class)->removeReceiptInventoryVoucher($receipt);
    }

    private function returnableDepotRows($depots)
    {
        $sourceDepotIds = $depots->pluck('id')->filter()->values();
        $returnedByDepot = $sourceDepotIds->isEmpty()
            ? collect()
            : Depot::query()
            ->select(
                'source_depot_id',
                DB::raw('SUM(ABS(entity)) as returned_quantity'),
                DB::raw('SUM(ABS(entity_sub_unit)) as returned_sub_unit'),
                DB::raw('SUM(ABS(weight)) as returned_weight')
            )
            ->whereIn('source_depot_id', $sourceDepotIds)
            ->whereHas('receipt', fn($query) => $query->where('document_status', '!=', 'canceled'))
            ->groupBy('source_depot_id')
            ->get()
            ->keyBy('source_depot_id');

        return $depots->map(function (Depot $depot) use ($returnedByDepot) {
            $returned = $returnedByDepot->get($depot->id);
            $depot->returned_quantity = (float) ($returned->returned_quantity ?? 0);
            $depot->returned_sub_unit = (float) ($returned->returned_sub_unit ?? 0);
            $depot->returned_weight = (float) ($returned->returned_weight ?? 0);
            $depot->returnable_quantity = max(0, abs((float) $depot->entity) - $depot->returned_quantity);
            $depot->returnable_sub_unit = max(0, abs((float) ($depot->entity_sub_unit ?: 0)) - $depot->returned_sub_unit);
            $depot->returnable_weight = max(0, abs((float) ($depot->weight ?: 0)) - $depot->returned_weight);

            return $depot;
        });
    }

    private function remainingReturnQuantity(Depot $sourceDepot): float
    {
        $returnedQuantity = (float) Depot::query()
            ->where('source_depot_id', $sourceDepot->id)
            ->whereHas('receipt', fn($query) => $query->where('document_status', '!=', 'canceled'))
            ->sum(DB::raw('ABS(entity)'));

        return max(0, abs((float) $sourceDepot->entity) - $returnedQuantity);
    }

    private function availableStockForDepot(Depot $depot): float
    {
        return (float) InventoryBalance::query()
            ->where('tenant_id', $depot->tenant_id)
            ->where('store_id', $depot->store_id)
            ->where('warehouse_location_id', $depot->warehouse_location_id ?: 0)
            ->where('product_id', $depot->pr_id)
            ->value('quantity');
    }

    private function returnSubUnit(Depot $sourceDepot, float $quantity, $submittedValue): float
    {
        $submitted = $this->decimalInput($submittedValue);

        if ($submitted !== null) {
            return $submitted;
        }

        $sourceQuantity = abs((float) $sourceDepot->entity);

        return $sourceQuantity > 0
            ? round(abs((float) ($sourceDepot->entity_sub_unit ?: 0)) * $quantity / $sourceQuantity, 3)
            : 0.0;
    }

    private function returnWeight(Depot $sourceDepot, float $quantity, $submittedValue): float
    {
        $submitted = $this->decimalInput($submittedValue);

        if ($submitted !== null) {
            return $submitted;
        }

        $sourceQuantity = abs((float) $sourceDepot->entity);

        return $sourceQuantity > 0
            ? round(abs((float) ($sourceDepot->weight ?: 0)) * $quantity / $sourceQuantity, 3)
            : 0.0;
    }

    private function nextReceiptNumber(): int
    {
        return ((int) Receipt::query()->max('number')) + 1;
    }

    private function weighbridgePayload(Request $request): array
    {
        $grossWeight = $this->decimalInput($request->gross_weight);
        $tareWeight = $this->decimalInput($request->tare_weight);
        $netWeight = $this->decimalInput($request->net_weight);

        if ($grossWeight !== null && $tareWeight !== null) {
            if ($grossWeight < $tareWeight) {
                throw ValidationException::withMessages([
                    'gross_weight' => 'وزن ناخالص باسکول نمی تواند کمتر از پارسنگ باشد.',
                ]);
            }

            $computedNetWeight = round($grossWeight - $tareWeight, 3);
            $netWeight = $netWeight !== null && abs($netWeight - $computedNetWeight) <= 0.005 ? $netWeight : $computedNetWeight;
        }

        return [
            'scale_ticket_number' => $request->scale_ticket_number ?: null,
            'vehicle_plate' => $request->vehicle_plate ?: null,
            'waybill_number' => $request->waybill_number ?: null,
            'gross_weight' => $grossWeight,
            'tare_weight' => $tareWeight,
            'net_weight' => $netWeight,
            'weighing_notes' => $request->weighing_notes ?: null,
        ];
    }

    private function decimalInput($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) str_replace(',', '', (string) $value), 3);
    }

    private function tracePayload(Request $request, int $index): array
    {
        return [
            'batch_no' => $this->traceValue($request, 'batch_no', $index),
            'lot_no' => $this->traceValue($request, 'lot_no', $index),
            'serial_no' => $this->traceValue($request, 'serial_no', $index),
            'manufactured_at' => $this->traceValue($request, 'manufactured_at', $index),
            'expiry_date' => $this->traceValue($request, 'expiry_date', $index),
            'color' => $this->traceValue($request, 'color', $index),
            'size' => $this->traceValue($request, 'size', $index),
            'quality_grade' => $this->traceValue($request, 'quality_grade', $index),
            'weight' => $this->traceValue($request, 'weight', $index),
            'tracking_notes' => $this->traceValue($request, 'tracking_notes', $index),
        ];
    }

    private function traceValue(Request $request, string $field, int $index)
    {
        $values = $request->get($field, []);

        if (!is_array($values)) {
            return $values ?: null;
        }

        return $values[$index] ?? null;
    }

    private function normalizedWarehouseLocationIds(Request $request, string $key, int $rowCount, string $label): array
    {
        $mode = TenantSettings::get('warehouse_location_mode', null, 'optional_locations');

        if ($mode === 'store_only') {
            return array_fill(0, $rowCount, 0);
        }

        $locationIds = $request->get($key, []);

        if ($mode === 'required_locations') {
            for ($index = 0; $index < $rowCount; $index++) {
                if (empty($locationIds[$index]) || (int) $locationIds[$index] <= 0) {
                    throw ValidationException::withMessages([
                        $key => "انتخاب {$label} برای همه ردیف های کالا الزامی است.",
                    ]);
                }
            }
        }

        return $locationIds;
    }
}
