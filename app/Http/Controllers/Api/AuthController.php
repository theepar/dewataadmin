<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user login and issue Sanctum token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required',
            'device_name' => 'required|string',
            'fcm_token'   => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
            ]);
        }

        // 1. Buat personal access token
        $token = $user->createToken($request->device_name)->plainTextToken;
        $role  = $user->roles->pluck('name')->first();

        // 2. Update/simpan device token (multi device support)
        if ($request->filled('fcm_token')) {
            \App\Models\DeviceToken::updateOrCreate(
                [
                    'user_id'     => $user->id,
                    'device_name' => $request->device_name,
                ],
                [
                    'fcm_token'   => $request->fcm_token,
                ]
            );
        }

        // 3. Catat login history
        $deviceName = $request->device_name ?? ($user->currentAccessToken()->name ?? null);

        // Jika device token ditemukan, user_agent diisi device_name (mobile), jika tidak pakai user agent asli
        $deviceToken = \App\Models\DeviceToken::where('user_id', $user->id)
            ->where('device_name', $deviceName)
            ->first();
        $userAgent = $deviceToken ? $deviceToken->device_name : $request->userAgent();

        \App\Models\LoginHistory::create([
            'user_id'     => $user->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $userAgent,
            'device_name' => $deviceName,
            'logged_in_at' => now(),
        ]);

        return response()->json([
            'message'      => 'Login berhasil!',
            'user'         => $user->only(['id', 'name', 'email']),
            'access_token' => $token,
            'role'         => $role,
        ]);
    }

    /**
     * Handle user logout (revoke current token).
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $deviceName = $request->input('device_name');
        $fcmToken   = $request->input('fcm_token');

        // Hapus token akses
        $current = $user->currentAccessToken();
        if ($current) {
            $current->delete();
        }

        // Hapus device token jika ada
        if ($deviceName && $fcmToken) {
            \App\Models\DeviceToken::where('user_id', $user->id)
                ->where('device_name', $deviceName)
                ->where('fcm_token', $fcmToken)
                ->delete();
        }

        return response()->json(['message' => 'Logout berhasil!']);
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users',
            'password'    => 'required|string|min:8|confirmed',
            'device_name' => 'required|string',
            'fcm_token'   => 'nullable|string',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign default role 'pegawai'
        $pegawaiRole = Role::where('name', 'pegawai')->first();
        if ($pegawaiRole) {
            $user->assignRole($pegawaiRole);
        }

        // Buat personal access token
        $token = $user->createToken($request->device_name)->plainTextToken;

        // Simpan device token jika ada
        if ($request->filled('fcm_token')) {
            \App\Models\DeviceToken::updateOrCreate(
                [
                    'user_id'     => $user->id,
                    'device_name' => $request->device_name,
                ],
                [
                    'fcm_token'   => $request->fcm_token,
                ]
            );
        }

        return response()->json([
            'message'      => 'Registrasi berhasil!',
            'user'         => $user->only(['id', 'name', 'email']),
            'access_token' => $token,
            'roles'        => $user->getRoleNames(),
        ], 201);
    }

    /**
     * Update FCM token for current device.
     */
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token'   => 'required|string',
            'device_name' => 'required|string',
        ]);

        $user = Auth::user();

        \App\Models\DeviceToken::updateOrCreate(
            [
                'user_id'     => $user->id,
                'device_name' => $request->device_name,
            ],
            [
                'fcm_token'   => $request->fcm_token,
            ]
        );

        return response()->json(['success' => true]);
    }
}
