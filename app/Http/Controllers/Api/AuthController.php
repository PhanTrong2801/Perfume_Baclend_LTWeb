<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. Đăng ký
    public function register(Request $request)
    {
        // Validate dữ liệu gửi lên
        $request->validate([
            'username' => 'required|string|unique:users',
            'full_name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        // Tạo user mới
        $user = User::create([
            'username' => $request->username,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer' // Mặc định là khách hàng
        ]);

        // Tạo token đăng nhập luôn
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng ký thành công',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // 2. Đăng nhập
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Kiểm tra thông tin
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json([
                'message' => 'Tài khoản hoặc mật khẩu không đúng'
            ], 401);
        }

        // Tìm user và tạo token
        $user = User::where('username', $request->username)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'token' => $token
        ]);
    }

    // 3. Đăng xuất (Xóa token)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đăng xuất thành công']);
    }
    
    // 4. Lấy thông tin user đang đăng nhập
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}