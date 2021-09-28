<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use \Tymon\JWTAuth\Exceptions\TokenInvalidException;
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

            return $next($request);
        } catch (Exception $e) {
            $message = '';

            if ($e instanceof TokenExpiredException) {
                $message = 'Token expired';
            } else if ($e instanceof TokenInvalidException) {
                $message = 'Token invalid';
            } else {
                $message = 'Token is required';
            }
            
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
