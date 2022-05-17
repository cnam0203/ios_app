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
use Illuminate\Support\Facades\Crypt;


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
            $iv = hex2bin("0123456789abcdef0123456789abcdef");
            $key =  hex2bin("abcdef9876543210abcdef9876543210");
            $encryptedToken = $request->bearerToken();
            $decryptedToken = openssl_decrypt($encryptedToken, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
            $decryptedToken = trim($decryptedToken);
            $decryptedToken = json_decode($decryptedToken, true);
            $userToken = $decryptedToken["userToken"];
            $user = JWTAuth::setToken($userToken)->toUser();
            $request['user'] = json_decode(json_encode($user));
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
                'message' => $message,
            ]);
        }
    }
}
