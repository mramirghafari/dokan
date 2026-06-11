<?php

namespace App\Http\Controllers;

use App\Models\CustomerPortalAccount;
use App\Models\CustomerPortalAnnouncement;
use App\Models\CustomerPortalPayment;
use App\Models\CustomerPortalRequest;
use App\Services\CustomerPortalGatewayService;
use App\Services\CustomerPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class CustomerPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['show', 'submitRequest', 'submitPayment', 'verifyPayment']);
        $this->middleware('can:customers,user')->only(['index', 'storeAccess', 'storeAnnouncement', 'updateRequest', 'updatePayment']);
    }

    public function index(CustomerPortalService $portalService)
    {
        return view('crm.customer_portal.index', $portalService->adminState(Auth::user()) + [
            'roles' => CustomerPortalAccount::ROLES,
            'statuses' => CustomerPortalAccount::STATUSES,
            'requestTypes' => CustomerPortalRequest::TYPES,
            'requestPriorities' => CustomerPortalRequest::PRIORITIES,
            'requestStatuses' => CustomerPortalRequest::STATUSES,
            'paymentStatuses' => CustomerPortalPayment::STATUSES,
            'paymentMethods' => CustomerPortalPayment::METHODS,
            'announcementAudiences' => CustomerPortalAnnouncement::AUDIENCES,
            'announcementPriorities' => CustomerPortalAnnouncement::PRIORITIES,
        ]);
    }

    public function storeAccess(Request $request, CustomerPortalService $portalService)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'role' => ['required', 'in:' . implode(',', array_keys(CustomerPortalAccount::ROLES))],
            'title' => ['nullable', 'string', 'max:180'],
            'contact_name' => ['nullable', 'string', 'max:180'],
            'contact_mobile' => ['nullable', 'string', 'max:80'],
            'contact_email' => ['nullable', 'email', 'max:180'],
            'expires_at' => ['nullable', 'date'],
        ]);

        [$account, $token] = $portalService->createAccess(Auth::user(), $data);

        Alert::success('دسترسی ساخته شد', 'لینک پورتال آماده شد: ' . route('customer-portal.show', $token));

        return redirect()->route('crm.customer-portal.index', ['portal_account' => $account->id]);
    }

    public function storeAnnouncement(Request $request, CustomerPortalService $portalService)
    {
        $data = $request->validate([
            'audience_type' => ['required', 'in:' . implode(',', array_keys(CustomerPortalAnnouncement::AUDIENCES))],
            'priority' => ['required', 'in:' . implode(',', array_keys(CustomerPortalAnnouncement::PRIORITIES))],
            'title' => ['required', 'string', 'max:180'],
            'body' => ['nullable', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $portalService->createAnnouncement(Auth::user(), $data);

        Alert::success('اطلاعیه ثبت شد', 'اطلاعیه برای پورتال مشتری/نماینده فعال شد.');

        return redirect()->route('crm.customer-portal.index');
    }

    public function updateRequest(Request $request, CustomerPortalRequest $portalRequest, CustomerPortalService $portalService)
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(CustomerPortalRequest::STATUSES))],
            'response' => ['nullable', 'string'],
        ]);

        $portalService->updateRequest(Auth::user(), $portalRequest, $data);

        Alert::success('درخواست بروزرسانی شد', 'وضعیت و پاسخ درخواست پورتال ثبت شد.');

        return redirect()->route('crm.customer-portal.index');
    }

    public function updatePayment(Request $request, CustomerPortalPayment $payment, CustomerPortalService $portalService)
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(CustomerPortalPayment::STATUSES))],
            'reference_number' => ['nullable', 'string', 'max:160'],
            'proof_text' => ['nullable', 'string'],
            'response' => ['nullable', 'string'],
        ]);

        $portalService->updatePayment(Auth::user(), $payment, $data);

        Alert::success('پرداخت بروزرسانی شد', 'وضعیت پرداخت پورتال و پاسخ قابل مشاهده برای مشتری ثبت شد.');

        return redirect()->route('crm.customer-portal.index');
    }

    public function show(string $token, CustomerPortalService $portalService)
    {
        return view('customer_portal.show', $portalService->publicState($token) + [
            'token' => $token,
            'requestTypes' => CustomerPortalRequest::TYPES,
            'requestPriorities' => CustomerPortalRequest::PRIORITIES,
            'paymentMethods' => CustomerPortalPayment::METHODS,
        ]);
    }

    public function submitRequest(Request $request, string $token, CustomerPortalService $portalService)
    {
        $data = $request->validate([
            'type' => ['required', 'in:' . implode(',', array_keys(CustomerPortalRequest::TYPES))],
            'priority' => ['required', 'in:' . implode(',', array_keys(CustomerPortalRequest::PRIORITIES))],
            'subject' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
            'requested_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $portalService->submitPublicRequest($token, $data);

        return redirect()->route('customer-portal.show', $token)->with('portal_success', 'درخواست شما ثبت شد.');
    }

    public function submitPayment(Request $request, string $token, CustomerPortalService $portalService, CustomerPortalGatewayService $gatewayService)
    {
        $data = $request->validate([
            'pishfactor_id' => ['nullable', 'integer'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:' . implode(',', array_keys(CustomerPortalPayment::METHODS))],
            'reference_number' => ['nullable', 'string', 'max:160'],
            'proof_text' => ['nullable', 'string', 'max:3000'],
        ]);

        $payment = $portalService->submitPayment($token, $data);

        if ($data['payment_method'] === 'online_gateway') {
            $gateway = $gatewayService->initiate($payment, route('customer-portal.payments.verify', [$token, $payment]));

            return redirect()->away($gateway['redirect_url']);
        }

        return redirect()->route('customer-portal.show', $token)->with('portal_success', 'پرداخت شما ثبت شد و بعد از بررسی وضعیت آن بروزرسانی می شود.');
    }

    public function verifyPayment(Request $request, string $token, CustomerPortalPayment $payment, CustomerPortalService $portalService, CustomerPortalGatewayService $gatewayService)
    {
        $verification = $gatewayService->verify($payment, $request);
        $portalService->verifyGatewayPayment($token, $payment, $verification);

        return redirect()->route('customer-portal.show', $token)->with('portal_success', $verification['message']);
    }
}
