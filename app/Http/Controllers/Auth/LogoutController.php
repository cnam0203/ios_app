<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully'
        ]);
    }
}
