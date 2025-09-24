<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $exist = DB::table('users')->where('email', $request->email)->first();

        $result = false;
        if ($exist) {
            $token = Str::random(60);

            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
            $content = '<p>Dear ' . $exist->name . ',</p>
            <br>
            <p>Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda. Jika Anda yang membuat permintaan ini, silakan klik tombol di bawah untuk mengatur ulang kata sandi Anda:</p>
            <a href="' . url('password/' . $token) . '">Reset Password</a>
            <p>Atau salin dan tempel tautan ini ke browser Anda:</p>
            <p>' . url('password/' . $token) . '</p>
            <br>
            <p>Demi keamanan, tautan ini akan kedaluwarsa dalam 5 menit. Jika Anda tidak meminta pengaturan ulang kata sandi, harap abaikan email ini—akun Anda tetap aman.</p>';
            $data = [
                'email' => $request->email,
                'content' => $content
            ];

            $result = sendEmail($data);
        } else {
            return back()->withErrors(['email' => 'User tidak terdaftar.']);
        }

        if ($result) {
            return back()->with('status', 'Kami telah mengirimkan tautan reset password ke email Anda.');
        } else {
            return back()->withErrors(['email' => 'Gagal mengirim email. Silakan coba lagi nanti.']);
        }
    }

    public function showResetForm(Request $request)
    {
        $data = DB::table('password_resets')->where('token', $request->token)->first();

        $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at);
        $exp = $created_at->addMinutes(5)->format('Y-m-d H:i:s');
        $now = now()->format('Y-m-d H:i:s');

        if ($now < $exp) {
            return view('auth.passwords.reset', compact('data'));
        } else {
            abort(419);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min' => 'Minimal password 8 karakter',
            'password.confirmed' => 'Password dan konfirmasi password tidak cocok.',
        ]);

        $data = DB::table('password_resets')->where('token', $request->token)->first();

        $user = DB::table('users')
            ->where('email', $data->email)
            ->update([
                'password' => bcrypt($request->password),
                'updated_at' => now()->format('Y-m-d H:i:s')
            ]);

        if ($user) {
            return redirect()->route('login')->with('status', 'Password berhasil direset, silahkan login');
        } else {
            return back()->withErrors(['email' => 'Password gagal direset']);
        }
    }
}