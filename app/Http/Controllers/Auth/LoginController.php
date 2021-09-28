<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AppController;
use Illuminate\Http\Request;
use App\User;
use JWTAuth;
use Exception;

class LoginController extends Controller
{
    public function login(Request $request){
        /* 
        After user is authenticated with Micro account on app
        User receives an access token from MAzure, then send to BE server
        BE server uses this access token to get user info from MAzure
        Then check whether user info is valid / matches with info in DB
         */
        $accessToken = $request['accessToken'];
        $url = 'https://graph.microsoft.com/v1.0/me';
        $headers = array("Authorization: Bearer ".$accessToken);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);   

        try {
            $infoResponse = curl_exec($curl);
            $result = json_decode($infoResponse, TRUE);
            
            // If response contains an error field, access token is invalid
            if (array_key_exists('error', $result)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid account',
                ]);
            } else {
                /* 
                Check whether email in response exists in DB
                If email exists, login successfully
                Create auth_token for next requests
                */
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
                    $appController = new AppController();
                    $menu = $appController->getMenu($user['id']);

                    return response()->json([
                        'status' => true,
                        'message' => 'User logged in successfully',
                        'jwtToken' => $token,
                        'userInfo' => ['name' => $user['name'], 'email' => $user['email']],
                        'menu' => $menu,
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
}
