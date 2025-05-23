<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WhatsappOtpController extends Controller
{
    // Kirim OTP ke WhatsApp
    public function sendOtp(Request $request)
    {
        // Validasi manual dengan Validator
        $validator = Validator::make($request->all(), [
            'phone' => 'required|exists:users,phone',
        ], [
            'phone.required' => 'Nomor handphone wajib diisi.',
            'phone.exists' => 'Nomor handphone tidak terdaftar.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }
        $phone = $request->phone;
        $otp = rand(1000, 9999);
        Cache::put('otp_'.$phone, $otp, now()->addMinutes(5));
        $message = "*$otp* adalah kode verifikasi Anda. Demi keamanan akun Anda, jangan berikan kode ini kepada siapapun.";
        $client = new Client;
        $apiEndpoint = env('WHATSAPP_API_ENDPOINT');
        try {
            $response = $client->post($apiEndpoint, [
                'query' => [
                    'apikey' => env('WHATSAPP_API_KEY'),
                    'sender' => env('WHATSAPP_SENDER_NUMBER'),
                    'receiver' => $phone,
                    'message' => $message,
                ],
            ]);
            if ($response->getStatusCode() !== 200) {
                Log::error('Gagal mengirim pesan WhatsApp: '.$response->getBody());

                return response()->json(['message' => 'Gagal mengirim OTP'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error WhatsApp OTP: '.$e->getMessage());

            return response()->json(['message' => 'Gagal mengirim OTP'], 500);
        }

        return response()->json(['message' => 'OTP berhasil dikirim']);
    }

    // Verifikasi OTP dan login
    public function verifyOtp(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'phone' => 'required|exists:users,phone',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }
        $phone = $request->phone;
        $otp = $request->otp;
        $cachedOtp = Cache::get('otp_'.$phone);
        if ($cachedOtp != $otp) {
            return response()->json(['message' => 'OTP salah atau kadaluarsa'], 401);
        }
        $user = User::where('phone', $phone)->first();
        Auth::login($user);
        Cache::forget('otp_'.$phone);
        $token = $user->createToken('whatsapp-otp')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user,
        ]);
    }
}
