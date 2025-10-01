<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Log; // Tambahkan untuk logging

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        // $request->validate([
        //     'email' => 'required|email|exists:users,email',
        // ]);

        $token = Str::random(60);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $data = [
            'email' => $request->email,
            'content' => 'Kami telah mengirimkan tautan reset password ke email Anda. Silakan periksa inbox Anda.' 
        ];

        $result = $this->sendEmail($data);

        if ($result) {
            return back()->with('status', 'Kami telah mengirimkan tautan reset password ke email Anda!');
        } else {
            return back()->withErrors(['email' => 'Gagal mengirim email. Silakan coba lagi nanti.']);
        }
    }

    public function sendEmail($data)
    {
        $O2W_HOST = env('O2W_HOST', '192.168.26.200');
        $O2W_PORT = env('O2W_PORT', '13119');
        $O2W_TO = env('O2W_TO', '60');
        $O2W_USER = env('O2W_USER', 'viossbe');
        $O2W_PWD = env('O2W_PWD', '489324BE6CB6962A9ADD43D3A617081F');
        $O2W_FROM = env('O2W_FROM', 'donotreply@masago.id');
        $O2W_SUB = env('O2W_SUB', 'BSB');

        $TGL = date('YmdHis');

        $ST = $this->getDigit(); 

        $CONTENT = $data['content'];
        $aSend = array(
            'PAN' => 'NOTS',
            'UID' => $ST,
            'DT' => $TGL,
            'PWD' => md5($ST . $O2W_PWD),
            'USERID' => $O2W_USER,
            'MSGTYPE' => '0',
            'DESTNUM' => $data['email'],
            'FROM' => $O2W_FROM,
            'SUBJECT' => $O2W_SUB,
            'CONTENT' => $CONTENT,
            'CONTENT_TYPE' => 'text/html',
            'SIGN' => ''
        );

        $sendReq = json_encode($aSend);
        $result = false;
        $timeoutStatus = $this->getRemoteResponse($O2W_HOST, $O2W_PORT, $O2W_TO, $sendReq, $Resp);

        // Logging untuk koneksi dan respons
        if ($timeoutStatus === 0) { 
            Log::info('Koneksi ke layanan email berhasil. Respons: ' . $Resp);
            // var_dump($Resp);
            // die;

            if (trim($Resp != '')) {
                $Response = json_decode($Resp);

                if ($Response->RC == '0000') {
                    $result = true;
                    Log::info('Email berhasil dikirim ke ' . $data['email']);
                } else {
                    Log::error('Email gagal dikirim. Kode respons: ' . $Response->RC); 
                }
            } else {
                Log::error('Tidak ada respons dari layanan email.');
            }
        } else {
            Log::error('Koneksi ke layanan email timeout.');
        }

        return $result;
    }

    public function getRemoteResponse($address, $port, $timeout, $message, &$sResp)
    {
        $s = '';
        $bTimeout = 0;

        try {
            $fp = @fsockopen($address, $port, $errno, $errstr, $timeout); 

            if (!$fp) {
                $s = $errstr . " (" . $errno . ")";
                $bTimeout = 1; // Set timeout jika koneksi gagal
            } else {
                fwrite($fp, $message, strlen($message));
                fwrite($fp, chr(-1));
                stream_set_timeout($fp, $timeout);
                $bDone = false;


                while ((!feof($fp)) && ($bTimeout == 0) && (!$bDone)) {
                    $info = @stream_get_meta_data($fp);

                    if ($info['timed_out']) {
                        $bTimeout = 1;
                    }
                    if ($bTimeout == 0) {
                        $c = fread($fp, 1);

                        if ($c != chr(-1)) {
                            $s .= $c;
                        } else {
                            $bDone = true;
                        }
                    }
                    // end of !$bTimeout
                }

                fclose($fp);
            }

            $sResp = $s;
            return $bTimeout; 

        } catch (Exception $e) {
            Log::error('Terjadi kesalahan saat mengirim email: ' . $e->getMessage());
            return 1; // Anggap timeout jika terjadi exception
        }
    }

    public function getDigit()
    {
        return rand(100000, 999999); 
    }
}