<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\PanelMembershipService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct(private PanelMembershipService $panels)
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * بررسی اعتبارسنجی و فعال‌بودن کاربر قبل از لاگین
     */
    protected function attemptLogin(Request $request)
    {
        $login = trim((string) $request->get($this->username()));
        $users = User::query()
            ->where(function ($query) use ($login) {
                $query->where('email', $login)
                    ->orWhere('username', $login)
                    ->orWhere('mobile', $login);
            })
            ->where('isActive', 1)
            ->get();

        $user = $users->first(function (User $candidate) use ($request) {
            if (!Hash::check($request->get('password'), $candidate->password)) {
                return false;
            }

            return $this->panels->accessiblePanelsForUser($candidate)->isNotEmpty();
        });

        if (!$user) {
            $blockedUser = $users->first(fn (User $candidate) => Hash::check($request->get('password'), $candidate->password));

            if ($blockedUser && $message = $blockedUser->loginBlockMessage()) {
                throw ValidationException::withMessages([
                    $this->username() => [$message],
                ]);
            }

            return false;
        }

        $this->guard()->login($user, $request->filled('remember'));

        return true;
    }

    protected function authenticated(Request $request, $user)
    {
        return $this->panels->redirectAfterLogin($user);
    }

    /**
     * مسیر بعد از لاگین موفق
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

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
