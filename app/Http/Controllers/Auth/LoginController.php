<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;

use Illuminate\Http\Request; // Tambahkan ini untuk menggunakan Request


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
            'captcha' => 'required|captcha',
        ], [
            'captcha.captcha'
            => 'Captcha yang Anda masukkan salah. Silakan coba lagi.',
        ]);

        $appEnv = getenv('APP_ENV');


        if ($this->attemptLogin($request)) {

            if ($appEnv === 'prod') {

                $user = auth()->user();

                // $forbiddenDomainForAdmin = 'bo-kbbs.test';
                $forbiddenDomainForAdmin = 'qrismerchant.kbbanksyariah.co.id';


                $currentUrl = $request->url();

                if (strpos($currentUrl, $forbiddenDomainForAdmin) !== false && $user->hasRole('Superadmin')) {
                    auth()->logout();

                    throw ValidationException::withMessages([
                        'email' => ['Admin tidak diizinkan login melalui domain ini.'],
                    ]);
                }
            }
            return $this->sendLoginResponse($request);
        } else {
            return $this->sendFailedLoginResponse($request);
        }
    }


    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
