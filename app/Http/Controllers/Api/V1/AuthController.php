<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user dan kembalikan Sanctum Bearer Token beserta Role & Permission Spatie.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        /** @var User|null $user */
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password yang Anda masukkan tidak valid.'],
            ]);
        }

        $deviceName = $credentials['device_name'] ?? 'ttc-nextjs-web';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message'     => 'Login berhasil.',
            'token'       => $token,
            'token_type'  => 'Bearer',
            'user'        => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'roles'       => $user->getRoleNames()->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
        ]);
    }

    /**
     * Ambil informasi user yang sedang login beserta daftar Role & Permission.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user'        => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'roles'       => $user->getRoleNames()->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
        ]);
    }

    /**
     * Logout & cabut token Sanctum saat ini.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}

