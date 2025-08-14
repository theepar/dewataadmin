<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

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
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
            ]);
        }

        // Hapus token lama untuk device yang sama
        $user->tokens()->where('name', $user->name.'-AuthToken')->delete();

        // Buat token baru
        $token = $user->createToken($user->name.'-AuthToken')->plainTextToken;
        $role  = $user->roles->pluck('name')->first(); // Ambil role utama user

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
        $request->user()->tokens()->delete();

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

        $token = $user->createToken($user->name.'-AuthToken')->plainTextToken;

        return response()->json([
            'message'      => 'Registrasi berhasil!',
            'user'         => $user->only(['id', 'name', 'email']),
            'access_token' => $token,
            'roles'        => $user->getRoleNames(),
        ], 201);
    }
}
