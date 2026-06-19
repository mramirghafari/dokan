<?php



namespace App\Http\Controllers;



use App\Models\DataExchangeRun;

use App\Services\DataExchangeService;

use App\Services\ProductBulkImportService;

use App\Services\TenantContextService;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use RealRashid\SweetAlert\Facades\Alert;

use Symfony\Component\HttpFoundation\StreamedResponse;



class ProductDataImportController extends Controller

{

    public function __construct()

    {

        $this->middleware('can:products,user')->only(['index', 'template', 'import', 'importStatus']);

    }



    public function index(Request $request)

    {

        $user = Auth::user();

        $tenantContext = app(TenantContextService::class);



        $recentImports = DataExchangeRun::query()

            ->where('entity_type', 'products')

            ->when((int) $user->isGod !== 1, function ($query) use ($user, $tenantContext) {

                $orgId = $tenantContext->organizationId($user);

                if ($orgId) {

                    $query->where('organization_id', $orgId);

                }

            })

            ->latest('id')

            ->limit(20)

            ->get();



        return view('products.data-import', [

            'recentImports' => $recentImports,

            'importColumnGuide' => app(ProductBulkImportService::class)->columnGuide(),

            'importService' => app(ProductBulkImportService::class),

        ]);

    }



    public function template(ProductBulkImportService $importService): StreamedResponse

    {

        $headers = $importService->templateHeaders();

        $samples = $importService->templateSampleRows();



        return response()->streamDownload(function () use ($headers, $samples) {

            $out = fopen('php://output', 'w');

            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, $headers);

            foreach ($samples as $sample) {

                fputcsv($out, $sample);

            }

            fclose($out);

        }, ProductBulkImportService::TEMPLATE_FILENAME, [

            'Content-Type' => 'text/csv; charset=UTF-8',

            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',

            'Pragma' => 'no-cache',

            'Expires' => '0',

        ]);

    }



    public function import(Request $request, DataExchangeService $exchangeService)

    {

        $request->validate([

            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'],

            'update_existing' => ['nullable', 'boolean'],

        ]);



        $path = $request->file('file')->store('imports/products', 'local');

        $run = $exchangeService->dispatchProductImport(Auth::user(), $path, [

            'update_existing' => $request->boolean('update_existing'),

        ], $request->file('file')->getClientOriginalName());



        $run = $run->fresh();



        if ($request->wantsJson() || $request->ajax()) {

            return response()->json($this->importStatusPayload($run));

        }



        $toast = $this->buildImportToast($run);



        Alert::success($toast['title'], $toast['message']);

        session()->flash('toast', [

            'type' => $toast['type'],

            'message' => $toast['message'],

        ]);



        return redirect()->route('products.data-import.index');

    }



    /**

     * @return array{type: string, title: string, message: string}

     */

    private function buildImportToast(DataExchangeRun $run): array

    {

        return match ($run->status) {

            'completed' => [

                'type' => 'success',

                'title' => 'ثبت موفق',

                'message' => sprintf(

                    'فایل با موفقیت پردازش شد — %s از %s ردیف ثبت شد.',

                    number_format((int) $run->success_rows),

                    number_format((int) $run->total_rows)

                ),

            ],

            'completed_with_errors' => [

                'type' => 'warning',

                'title' => 'تکمیل با خطا',

                'message' => sprintf(

                    'پردازش انجام شد اما %s ردیف خطا داشت — %s ردیف موفق.',

                    number_format((int) $run->failed_rows),

                    number_format((int) $run->success_rows)

                ),

            ],

            'failed' => [

                'type' => 'danger',

                'title' => 'خطا در Import',

                'message' => $run->error_message ?: 'پردازش فایل ناموفق بود.',

            ],

            default => [

                'type' => 'info',

                'title' => 'در صف پردازش',

                'message' => 'فایل در صف پردازش قرار گرفت. وضعیت در جدول زیر قابل پیگیری است.',

            ],

        };

    }



    public function importStatus(DataExchangeRun $run)

    {

        $user = Auth::user();



        if ((int) $user->isGod !== 1 && (int) $run->organization_id !== (int) app(TenantContextService::class)->organizationId($user)) {

            abort(403);

        }



        abort_unless($run->entity_type === 'products', 404);



        return response()->json($this->importStatusPayload($run));

    }



    /**

     * @return array<string, mixed>

     */

    private function importStatusPayload(DataExchangeRun $run): array

    {

        $importService = app(ProductBulkImportService::class);

        $errorSamples = $importService->summarizeRowErrors($run->summary_json ?? []);



        return array_merge([

            'id' => $run->id,

            'run_id' => $run->id,

            'exchange_run_id' => $run->id,

            'status' => $run->status,

            'total_rows' => $run->total_rows,

            'success_rows' => $run->success_rows,

            'failed_rows' => $run->failed_rows,

            'error_message' => $run->error_message,

            'error_samples' => $errorSamples,

            'summary' => $run->summary_json,

        ], $this->importProgressPayload($run));

    }



    /**

     * @return array{progress_percent: int, stage: string, stage_label: string, processed_rows: int, detail_message?: string}

     */

    private function importProgressPayload(DataExchangeRun $run): array

    {

        $total = max(0, (int) $run->total_rows);

        $success = max(0, (int) $run->success_rows);

        $failed = max(0, (int) $run->failed_rows);

        $skipped = max(0, (int) ($run->summary_json['skipped'] ?? 0));

        $processed = $success + $failed + $skipped;

        $detailMessage = $this->importDetailMessage($run);



        if ($run->status === 'completed') {

            return [

                'progress_percent' => 100,

                'stage' => 'completed',

                'stage_label' => 'ثبت با موفقیت انجام شد',

                'processed_rows' => $processed,

                'detail_message' => $detailMessage,

            ];

        }



        if ($run->status === 'completed_with_errors') {

            return [

                'progress_percent' => 100,

                'stage' => 'completed_with_errors',

                'stage_label' => 'تکمیل با خطا',

                'processed_rows' => $processed,

                'detail_message' => $detailMessage,

            ];

        }



        if ($run->status === 'failed') {

            return [

                'progress_percent' => 100,

                'stage' => 'failed',

                'stage_label' => 'پردازش ناموفق',

                'processed_rows' => $processed,

                'detail_message' => $detailMessage,

            ];

        }



        if ($total > 0) {

            $percent = min(95, 35 + (int) round(($processed / $total) * 60));



            return [

                'progress_percent' => $percent,

                'stage' => 'importing',

                'stage_label' => sprintf('ثبت محصولات — %s از %s ردیف', number_format($processed), number_format($total)),

                'processed_rows' => $processed,

            ];

        }



        return [

            'progress_percent' => 28,

            'stage' => 'reading',

            'stage_label' => 'خواندن و تحلیل فایل',

            'processed_rows' => 0,

        ];

    }



    private function importDetailMessage(DataExchangeRun $run): string

    {

        if ($run->error_message) {

            return $run->error_message;

        }



        if ($run->status === 'completed') {

            return sprintf(

                '%s از %s ردیف با موفقیت ثبت شد.',

                number_format((int) $run->success_rows),

                number_format((int) $run->total_rows)

            );

        }



        if ($run->status === 'completed_with_errors') {

            $failedRows = (int) $run->failed_rows;

            $successRows = (int) $run->success_rows;

            $samples = app(ProductBulkImportService::class)->summarizeRowErrors($run->summary_json ?? [], 3);

            $base = sprintf(

                '%s ردیف موفق — %s ردیف خطا از %s.',

                number_format($successRows),

                number_format($failedRows),

                number_format((int) $run->total_rows)

            );



            if ($samples === []) {

                return $base;

            }



            $hints = collect($samples)

                ->map(fn (array $sample) => sprintf('%s (%s×)', $sample['message'], number_format($sample['count'])))

                ->implode('؛ ');



            return $base . ' نمونه خطا: ' . $hints;

        }



        return 'در حال پردازش فایل...';

    }

}


