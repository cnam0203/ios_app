<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request){
        $accessToken = $request['accessToken'];

        $url = 'https://graph.microsoft.com/v1.0/me';
        $headers = array(
            "Authorization: Bearer ".$accessToken,
         );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);   

        try {
            $identityResponse = curl_exec($curl);

            $result = json_decode($identityResponse, TRUE);
            
            if(array_key_exists('error', $result)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid account',
                ]);
            } else {
                try {
                    $email = $result['userPrincipalName'];
                    $user = User::where('email', $email)->first();

                    if (is_null($user)) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Invalid account',
                        ]);
                    }


                    else {
                        JWTAuth::factory()->setTTL(10);
                        $token = JWTAuth::fromUser($user);

                        return response()->json([
                            'status' => true,
                            'message' => 'User logged in successfully',
                            'jwtToken' => $token,
                            'userInfo' => ['name' => $user['name'], 'email' => $user['email']],
                        ]);
                    }
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    return response()->json([
                        'status' => false,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        } catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'User logged in failed',
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => false,
                'message' => 'User logged out successfully'
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'User logged out successfully'
            ], 500);
        }
    }
}
