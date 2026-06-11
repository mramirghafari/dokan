<?php

namespace App\Http\Middleware;

use App\Models\License;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class LicenseCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Artisan::call('optimize');
        \Artisan::call('config:clear');


        $license = License::where('id', 1)->first();
        $result = $this->send($license->api, $license->username, $license->order_id, $license->domain, $license->product_id);

        switch ($result) {
            case '1':
                $error = NULL;
                break;
            case '-1':
                $error = 'API اشتباه است';
                break;
            case '-2':
                $error = 'نام کاربری اشتباه است';
                break;
            case '-3':
                $error = 'کد سفارش اشتباه است';
                break;
            case '-4':
                $error = 'کد سفارش قبلاً ثبت شده است';
                break;
            case '-5':
                $error = 'کد سفارش مربوطه به این نام کاربری نمیباشد.';
                break;
            case '-6':
                $error = 'اطلاعات وارد شده  در فرمت صحیح نمیباشند!';
                break;
            case '-7':
                $error = 'کد سفارش مربوط به این محصول نیست';
                break;
            case '-8':
                $error = 'کد سفارش مربوطه به این نام کاربری نمیباشد.';
                break;
            default:
                $error = 'خطای غیرمنتظره رخ داده است';
                break;
        }

        if ($error != NULL) {
            return response()->view(view: 'lic', data: compact('error'));
        } else {

            return $next($request);
        }



    }


    public function send($api, $username, $order_id, $domain, $productId)
    {
        $url = 'https://www.rtl-theme.com/oauth/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "api=$api&username=$username&order_id=$order_id&domain=$domain&pid=$productId");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;

    }

}
