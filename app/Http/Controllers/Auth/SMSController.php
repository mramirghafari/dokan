<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Notifs;
use App\Models\SmsVerification;
use App\Models\User;
use App\Services\PanelMembershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Kavenegar;

class SMSController extends Controller
{
    public function __construct(private PanelMembershipService $panels)
    {
    }

    public function index(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'mobile' => ['required', 'regex:/^(09)[0-9]{9}/'],
            ],
            [
                'mobile' => 'شماره موبایل باید 11 رقم و با 09 شروع شود',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }


        $mobile = $request->mobile;
        $random_code = rand(1111, 9999);
        try {
            $accessiblePanels = $this->panels->accessiblePanelsForMobile($mobile);

            if ($accessiblePanels->isEmpty()) {
                return redirect()->back()->with('error', 'کاربری با این شماره وجود ندارد یا دسترسی فعالی ندارد.');
            }

            $user = User::query()->find($accessiblePanels->first()['user_id']);

            if (!$user) {
                return redirect()->back()->with('error', 'کاربری با این شماره وجود ندارد.');
            }

            $sms_before = SmsVerification::where('contact_number', $mobile)->where('status', 0)->first();

            if ($sms_before == null || $sms_before->created_at->diffInMinutes(Carbon::now()) > 2) {
                SmsVerification::where('contact_number', $mobile)->where('status', 0)->update(['status' => 2]);

                $msg = 'کد تایید با موفقیت برای شما ارسال شد';
                $sms = new SmsVerification();
                $sms->contact_number = $mobile;
                $sms->code = $random_code;
                $sms->status = 0;
                $sms->user_id = $user->id;
                $sms->save();

                $template = "OTPdaramino";
                $type = "sms";
                $token = $random_code;
                $token2 = "";
                $token3 = "";
                $result = Kavenegar::VerifyLookup($mobile, $token, $token2, $token3, $template, $type);
                session(['mobile' => $mobile]);

                return redirect(route('userOTP'))->with('msg', $msg)->with('mobilee', $mobile)->with('sendSmsok', 'sendSmsok');
            } else {
                return redirect()->back()->with('error', 'کد تایید قبلی شما هنوز معتبر است.');
            }
        } catch (\Exception $e) {
            //dd($e);

            return redirect()->back()->with('error', 'ارسال کد با خطا مواجه شد لطفا بعدا تلاش فرمایید');
            // return redirect()->back()->with('msg', 'ارسال کد با خطا مواجه شد لطفا بعدا تلاش فرمایید');
        }
    }


    public function otp()
    {

        if (session()->has('mobile')) {
            $mobile = session('mobile');
            $sms_before = SmsVerification::where('contact_number', $mobile)->where('status', 0)->first();
            if ($sms_before != null && $sms_before->created_at->diffInMinutes(Carbon::now()) < 2) {
                return view('auth.otp');
            }
        }

        return redirect(route('login'));
    }

    public function vilidation_code(Request $request)
    {

        //dd($request->all());

        $mobile = session('mobile');
        $code = implode("", $request->otp);


        $sms_before = SmsVerification::where('contact_number', $mobile)->where('status', 0)->first();

        if ($sms_before == null || $sms_before->created_at->diffInMinutes(Carbon::now()) > 2) {
            return redirect()->back()->with('error', 'کد وارد شده منقضی شده است.');
        } else if ($sms_before->code != $code) {
            return redirect()->back()->with('error', 'کد وارد شده صحیح نمیباشد.');
        } else if ($sms_before->code == $code) {
            $User = User::where('mobile', $mobile)->where('id', $sms_before->user_id)->first();

            if (!$User || $message = $User->loginBlockMessage()) {
                return redirect(route('login'))->with('error', $message ?: 'امکان ورود برای این کاربر وجود ندارد.');
            }

            $sms_before = SmsVerification::where('contact_number', $mobile)->where('status', 0)->update(['status' => 1]);
            Auth::login($User);

            $Notif = new Notifs();
            $Notif->user_id = auth()->user()->id;
            $Notif->title = "ورود موفقیت آمیز";
            $Notif->content = "گزارش ورود شما با موفقیت ثبت شد.";
            $Notif->save();

            return $this->panels->redirectAfterLogin($User);
        }
    }
}
