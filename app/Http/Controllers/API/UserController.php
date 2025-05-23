<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    use PasswordValidationRules;


    /**
     * User - Fetch profile ✅
     */
    public function profile(Request $request)
    {
        try {
            $user = User::with(['cluster', 'region', 'role', 'divisi', 'badanusaha'])
                ->where('id', Auth::user()->id)
                ->first();

            return ResponseFormatter::success(
                ['user' => $user->formatForAPI(), 'message' => 'Data profil pengguna berhasil diambil'],
                'Fetch profile success'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }


    /**
     * User - Login ✅
     *
     * @unauthenticated
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'version' => 'required|string|in:1.0.3',
            'username' => 'required|string',
            'password' => 'required|string',
            'notif_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $userMessage = 'Periksa kembali data yang Anda masukkan.';

            if ($errors->has('version')) {
                $userMessage = 'Perbarui aplikasi SAM Anda ke versi terbaru.';
            }
            if ($errors->has('notif_id')) {
                $userMessage = 'Pastikan perangkat Anda terhubung dengan benar.';
            }

            return ResponseFormatter::error([
                'errors' => $errors,
                'message' => $userMessage
            ], 'Invalid Input', 422);
        }
        try {
            $credentials = request(['username', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Cek kembali username dan password anda'
                ], 'Unauthorized', 500);
            }

            $user = User::with(['region', 'cluster', 'role', 'divisi', 'badanusaha', 'tm'])
                ->where('username', $request->username)
                ->first();

            if (!Hash::check($request->password, $user->password)) {
                throw new Exception('Invalid Credentials');
            }

            $user->id_notif = $request->notif_id;
            $user->update();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user->formatForAPI(),
            ], 'Authenticated');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }

    /**
     * User - Logout ✅
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return ResponseFormatter::success(
                ['message' => 'Anda telah berhasil keluar dari aplikasi'],
                'Token Revoked'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }
}
