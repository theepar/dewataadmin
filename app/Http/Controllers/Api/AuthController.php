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
            'device_name' => 'required|string', // [WAJIBKAN device_name]
            'fcm_token'   => 'nullable|string', // [OPSIONAL fcm_token]
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
            ]);
        }

        // Buat token baru
        $token = $user->createToken($request->device_name)->plainTextToken;
        $role  = $user->roles->pluck('name')->first();

        // [UPDATE FCM TOKEN JIKA ADA]
        if ($request->filled('fcm_token')) {
            $user->fcm_token = $request->input('fcm_token');
            $user->save();

            // Simpan juga ke tabel device_tokens (multi device support)
            \App\Models\DeviceToken::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'device_name' => $request->device_name,
                ],
                [
                    'fcm_token' => $request->fcm_token,
                ]
            );
        }

        // Ambil device name dari token jika ada
        $deviceName = $request->device_name
            ?? ($user->currentAccessToken()->name ?? null);

        // Tambahkan log login history
        \App\Models\LoginHistory::create([
            'user_id'     => $user->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
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
        $fcmToken = $request->input('fcm_token');

        // Hapus token akses
        $current = $user->currentAccessToken();
        if ($current) {
            $current->delete();
        }

        // Hapus device token jika ada
        if ($deviceName && $fcmToken) {
            \App\Models\DeviceToken::where('user_id', $user->id)
                ->where('fcm_token', $fcmToken)
                ->where('device_name', $deviceName)
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
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'device_name' => 'required',
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

        $token = $user->createToken($request->device_name)->plainTextToken;

        // Setelah createToken di register
        if ($request->filled('fcm_token')) {
            \App\Models\DeviceToken::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'device_name' => $request->device_name,
                ],
                [
                    'fcm_token' => $request->fcm_token,
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

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $user = Auth::user();

        \App\Models\DeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_name' => $request->device_name,
            ],
            [
                'fcm_token' => $request->fcm_token,
            ]
        );

        return response()->json(['success' => true]);
    }
}
