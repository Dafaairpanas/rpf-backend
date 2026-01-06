<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login user dan buat token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Log failed login attempt
            AuditLog::logLogin($request->email, false);

            return ApiResponse::unauthorized('Kredensial tidak valid');
        }

        // Log successful login
        AuditLog::logLogin($request->email, true, $user->id);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('role'),
        ], 'Login berhasil');
    }

    /**
     * Logout user (hapus token)
     */
    public function logout(Request $request)
    {
        // Log logout
        AuditLog::logLogout($request->user()->id);

        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Berhasil logout');
    }

    /**
     * Ambil data user yang sedang login
     */
    public function me(Request $request)
    {
        return ApiResponse::success($request->user()->load('role'));
    }
}

