<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use JWTAuth;

class VerifyJWTToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['user'] = $user;
        } catch (Exception $e) {
            error_log($e->getMessage());
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token expired',
                ]);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token invalid',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Token is required'
                ]);
            }
        }
        return $next($request);
    }
}
