<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * مسیر بعد از لاگین موفق
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * ایجاد کنترلر
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * بررسی اعتبارسنجی و فعال‌بودن کاربر قبل از لاگین
     */
    protected function attemptLogin(Request $request)
    {
        $login = trim((string) $request->get($this->username()));
        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->orWhere('mobile', $login)
            ->first();

        if ($user && $message = $user->loginBlockMessage()) {
            throw ValidationException::withMessages([
                $this->username() => [$message],
            ]);
        }

        if (!$user || !Hash::check($request->get('password'), $user->password)) {
            return false;
        }

        $this->guard()->login($user, $request->filled('remember'));

        return true;
    }

    /**
     * پارامترهای اعتبارسنجی کاربر
     */
    protected function credentials(Request $request)
    {
        return [
            'email' => $request->get($this->username()),
            'password' => $request->get('password'),
        ];
    }
}
