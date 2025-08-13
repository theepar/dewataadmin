<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Pastikan model User Anda diimpor
use Illuminate\Validation\ValidationException;

// Untuk penanganan error validasi

class AuthController extends Controller
{
    /**
     * Handle user login and issue Sanctum token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required',
            'device_name' => 'required', // Nama perangkat dari aplikasi mobile
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
            ]);
        }

        // Hapus token lama jika ada (opsional, tergantung kebijakan Anda)
        $user->tokens()->where('name', $request->device_name)->delete();

        // Buat token baru
        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'user'    => $user->only('id', 'name', 'email'), // Kirim data user yang relevan
            'token'   => $token,
            'roles'   => $user->getRoleNames(), // Kirim peran user
        ]);
    }

    /**
     * Handle user logout (revoke current token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil!']);
    }

    // Anda bisa tambahkan method register() jika aplikasi mobile bisa mendaftar user baru

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Otomatis berikan peran 'pegawai' untuk user baru yang mendaftar via API
        $pegawaiRole = \Spatie\Permission\Models\Role::where('name', 'pegawai')->first();
        if ($pegawaiRole) {
            $user->assignRole($pegawaiRole);
        }

        $token = $user->createToken($request->device_name ?? 'mobile-device')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil!',
            'user'    => $user->only('id', 'name', 'email'),
            'token'   => $token,
            'roles'   => $user->getRoleNames(),
        ], 201);
    }
}
