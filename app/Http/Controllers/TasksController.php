<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use Illuminate\Http\Request;
use App\Models\Customers;
use App\Models\Pishfactor;
use App\Models\Tasks;
use App\Models\Region;
use App\Models\Area;
use App\Models\Role;
use App\Models\User;
use App\Services\TenantSettings;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Kavenegar;


class TasksController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:tasks,user')->only(['index', 'store', 'edit', 'update', 'show', 'destroy', 'active_list']);
        $this->middleware(function ($request, $next) {
            if (!TenantSettings::enabled('feature_route_management')) {
                Alert::warning('غیرفعال', 'مدیریت مسیر برای این پنل غیرفعال است');
                return redirect()->route('index');
            }

            return $next($request);
        });
    }

    public function index()
    {
        $user = \Auth::user();

        if ($user->isGod == 1) {
            $Regions = Region::all();
            $Tasks = Tasks::where('status', 0)->get();
        } elseif ($user->isAdmin == 1) {
            $Regions = Region::forOrganizations($user)->get();
            $Tasks = Tasks::forOrganizations($user)->where('status', 0)->get();
        } else {
            $Regions = Region::forOrganizations($user)->where('leader_id', $user->id)->get();
            //$Tasks = Tasks::where('leader_id',$user->id)->get();
            $Tasks = Tasks::forOrganizations($user)->where('leader_id', $user->id)->where('status', 0)->get();
        }

        $Users = $this->getAssignableVisitors($user);

        return view('tasks.index', compact('Tasks', 'Regions', 'Users'));
    }

    public function create()
    {

        $user = \Auth::user();

        if ($user->isAdmin == 1) {
            $Regions = Region::forOrganizations($user)->get();
        } else {
            $Regions = Region::forOrganizations($user)->where('leader_id', $user->id)->get();
        }

        $Users = $this->getAssignableVisitors($user);

        return view('tasks.add-task', compact('Regions', 'Users'));
    }

    public function getVisitorsByRegion($region_id)
    {
        $user = \Auth::user();
        $region = Region::findOrFail($region_id);
        $users = $this->getAssignableVisitors($user, $region)
            ->map(function ($visitor) {
                return [
                    'id' => $visitor->id,
                    'name' => $visitor->name,
                ];
            })
            ->values();

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $user = \Auth::user();

        $request['leader_id'] = $user->id;
        $Tasks = Tasks::create($request->all());
        $user = \Auth::user();

        try {
            $Uservisitor = User::find($request->user_id);
            $Cur_Area = Area::find($request->area_id);
            $area_name = str_replace(" ", "", $Cur_Area->name);
            $receptor = $Uservisitor->mobile;
            $token = "$area_name";
            $token2 = "$request->date";
            $token3 = "";
            $template = "VisitorNotif";
            //Send null for tokens not defined in the template
            //Pass token10 and token20 as parameter 6th and 7th
            $result = Kavenegar::VerifyLookup($receptor, $token, $token2, $token3, $template, $type = null);
            if ($result) {
                foreach ($result as $r) {
                    echo "messageid = $r->messageid";
                    echo "message = $r->message";
                    echo "status = $r->status";
                    echo "statustext = $r->statustext";
                    echo "sender = $r->sender";
                    echo "receptor = $r->receptor";
                    echo "date = $r->date";
                    echo "cost = $r->cost";
                }
            }
        } catch (\Kavenegar\Exceptions\ApiException $e) {
            // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
            echo $e->errorMessage();
        } catch (\Kavenegar\Exceptions\HttpException $e) {
            // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
            echo $e->errorMessage();
        }

        ActivityLogService::safeLog('create', "مسیر جدید برای ویزیتور ایجاد شد.", null, ['section' => 'system', 'event_key' => 'system.create']);

        Alert::success('تشکر', "مسیر جدید برای ویزیتور ایجاد شد.");
        return back();
    }

    public function show($id) {}

    public function edit(Tasks $Task)
    {


        $user = \Auth::user();

        if ($user->isAdmin == 1) {
            $Regions = Region::all();
            $Tasks = Tasks::all();
        } else {
            $Regions = Region::where('leader_id', $user->id)->get();
            $Tasks = Tasks::all();
        }

        $Users = $this->getAssignableVisitors($user);


        $Cur_area = Area::find($Task->area_id);
        if (isset($Cur_area) && $Cur_area != null) {
            $Cur_Region = Region::find($Cur_area->region_id);
            $Cur_areas = Area::where('region_id', $Cur_Region->id)->get();
        } else {
            $Cur_Region = Region::where('leader_id', $user->id)->get();
            $Cur_areas = null;
        }


        return view('tasks.edit', compact('Tasks', 'Regions', 'Users', 'Task', 'Cur_area', 'Cur_areas', 'Cur_Region', 'VisitorUsers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tasks $Task)
    {

        $user = \Auth::user();
        $request->status == "on" ? $request->status = 1 : $request->status = 0;
        $Task->update([
            'leader_id' => $user->id,
            'user_id' => $request->user_id,
            'area_id' => $request->area_id,
            'date' => $request->date,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'senf' => $request->senf,
            'channel' => $request->channel,
            'min_sale_item' => $request->min_sale_item,
            'min_sale_price' => $request->min_sale_price,
            'min_sale_item_price' => $request->min_sale_item_price,
            'status' => $request->status,
        ]);
        $user = \Auth::user();
        ActivityLogService::safeLog('update', 'مسیر ویرایش شد', null, ['section' => 'system', 'event_key' => 'system.update']);

        Alert::success('تشکر', 'مسیر با موفقیت ویرایش شد');
        return back();
    }

    public function MyTask(Tasks $task, Request $request)
    {

        $User = \Auth::user();
        $Customers = Customers::where('area', $task->area_id)->get();
        $task_area = DB::table('areas')->where('id', $task->area_id)->first();
        $task_region = DB::table('regions')->where('id', $task_area->region_id)->first();
        $Factors_In_Task = Pishfactor::where('visitor_id', $User->id)->where('task_id', $task->id)->count();

        if ($request->has('lat') && $request->has('long')) {
            $lat = $request->input('lat');
            $long = $request->input('long');
            $Destinations = "";
            $totalCus = count($Customers);
            $counter = 0;
            $validCustomers = [];
            $invalidCustomers = [];
            foreach ($Customers as $Customer) {
                if ($Customer->shop_lat != null && $Customer->shop_lng != null) {
                    $Destinations .= $Customer->shop_lat . "%2C" . $Customer->shop_lng;
                    $validCustomers[] = $Customer;
                    if ($counter !== $totalCus) {
                        $Destinations .= "%7C";
                    }
                } else {
                    $invalidCustomers[] = $Customer;
                }
                $counter++;
            }

            // echo $Destinations;
            // die();

            if (str_ends_with($Destinations, '%7C')) {
                $Destinations = substr($Destinations, 0, -3);
            }

            // echo "https://api.neshan.org/v1/distance-matrix?type=car&origins=$lat%2C$long&destinations=$Destinations";
            //  echo "<hr />";
            // die();

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.neshan.org/v1/distance-matrix?type=car&origins=$lat%2C$long&destinations=$Destinations",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Api-key: service.e0404239fbd245a3aa943ad68eb3feda'
                ),
            ));
            $response = json_decode(curl_exec($curl));
            curl_close($curl);

            //  dd($response);


            $elements = $response->rows[0]->elements;
            $destination_addresses = $response->destination_addresses;
            //dd($destination_addresses);

            $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

            $index = 0;
            foreach ($validCustomers as $customer) {
                $customer->distance = $elements[$index]->distance->text ?? null;
                $distancee =  str_replace($persianDigits, $englishDigits, $elements[$index]->distance->text);
                $customer->distance_without_text = intval($distancee) ?? null;
                $customer->duration = $elements[$index]->duration->text ?? null;
                $durationn =  str_replace($persianDigits, $englishDigits, $elements[$index]->duration->text);
                $customer->duration_without_text = intval($durationn) ?? null;
                $customer->distance_value = $elements[$index]->distance->value ?? null;
                $customer->duration_value = $elements[$index]->duration->value ?? null;

                $index++;
            }

            usort($validCustomers, function ($a, $b) {
                return $a->duration_without_text <=> $b->duration_without_text;
            });
        } else {
            $validCustomers = [];
            $invalidCustomers = [];
        }



        return view('tasks.mytask', compact('task', 'Customers', 'task_region', 'task_area', 'Factors_In_Task', 'validCustomers', 'invalidCustomers'));
    }

    public function active_list()
    {
        $user = \Auth::user();
        $role = Role::where('title', 'visitor')->first();

        if ($user->isGod == 1) {
            $Regions = Region::all();
            $Tasks = Tasks::where('status', 1)->get();
        } elseif ($user->isAdmin == 1) {
            // $Regions = Region::forOrganizations($user)->get();
            $Users = User::forOrganizations($user)->pluck('id');
            $Tasks = Tasks::forOrganizations($user)->where(function ($query) use ($Users) {
                $query->whereIn('user_id', $Users)
                    ->orWhereIn('leader_id', $Users);
            })
                ->where('status', 1)
                ->get();
        } else {
            // $Regions = Region::where('leader_id',$user->id)->get();
            $Tasks = Tasks::forOrganizations($user)->where('leader_id', $user->id)->where('status', 1)->get();
        }


        return view('tasks.active_list', compact('Tasks'));
    }

    public function MyTasks()
    {

        $user = \Auth::user();

        $MyTasks = Tasks::forOrganizations($user)->where('status', 1)->where(function ($query) use ($user) {
            $query->where('user_id', $user->id)->orWhere('leader_id', $user->id);
        })->orderBy('id', 'desc')->get();
        return view('tasks.MyTasks', compact('MyTasks'));
    }

    public function CustomerInfo(Customers $Customer)
    {

        $user = \Auth::user();

        $Regions = Region::all();
        $Cur_Area = Area::find($Customer->area);
        $Cur_Region = Region::find($Cur_Area->region_id);
        $This_areas = Area::where('region_id', $Cur_Area->region_id)->get();

        $CustomerFactors = Pishfactor::where('customer_id', $Customer->id)->where('visitor_id', $user->id)->get();

        return view('tasks.mytask_customerinfo', compact('Customer', 'Regions', 'Cur_Area', 'Cur_Region', 'This_areas', 'CustomerFactors'));
    }

    private function getAssignableVisitors(User $user, ?Region $region = null)
    {
        $visitorRoleId = Role::where('title', 'visitor')->value('id');

        if (!$visitorRoleId) {
            return collect();
        }

        $visitorUserIds = DB::table('role_user')
            ->where('role_id', $visitorRoleId)
            ->pluck('user_id')
            ->toArray();

        $query = User::whereIn('id', $visitorUserIds)
            ->where('isActive', 1);

        $roleTitles = $user->roles->pluck('title')->toArray();
        $isLeader = in_array('leader', $roleTitles, true);
        $isManager = in_array('expert', $roleTitles, true);

        if ($region) {
            if ($region->organization_id) {
                $organizationFilterUser = new User();
                $organizationFilterUser->organization_id = $region->organization_id;
                $query->forOrganizations($organizationFilterUser);
            }

            $regionLeaderIds = $this->extractLeaderIds($region->leader_id);
            if (!empty($regionLeaderIds)) {
                return $query->whereIn('leader_id', $regionLeaderIds)->get();
            }

            return $query->get();
        }

        if ($user->isGod == 1) {
            return $query->get();
        }

        if ($user->isAdmin == 1 || $isManager) {
            return $query->forOrganizations($user)->get();
        }

        if ($isLeader) {
            return $query->where('leader_id', $user->id)->get();
        }

        return $query->forOrganizations($user)->get();
    }

    private function extractLeaderIds($leaderId): array
    {
        if (empty($leaderId)) {
            return [];
        }

        $decoded = json_decode($leaderId, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded));
        }

        return array_values(array_filter([$leaderId]));
    }
}
