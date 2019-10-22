<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use App\Services\Auth\AuthRequests;
use App\User;
use App\Http\Requests\User\Register as RegisterRequest;


class AuthController extends \App\Http\Controllers\Controller
{

    protected function guard()
    {
        return Auth::guard('api');
    }

    /**
     * API Login, on success return JWT Auth token
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request,$additional_params = null)
    {

        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
        ];

        try {
            // attempt to verify the credentials and create a token for the user
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'data'   => 'The username or password you have entered is invalid.',
                ], 401);
            } else {
                $response   =  (new AuthRequests)->login($request->email,$request->password);

                if(!$response){
                    return response()->json(['success' => false, 'data' => 'Please check your credentials'], 500);
                }
                else{
                    return response()->json(['success' => true, 'token' => $response], 200);

                }
                
            }
        } catch (\Exception $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json([
                'success' => false,
                'data'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API Register
     *
     * @param RegisterRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $data = [
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'username'  =>  $request->username,
            'name'      =>  $request->name,
        ];

        try {
            $user = User::create($data);


            return $this->login($request,true);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => 'Oops, something went wrong!'], 500);
        }


    }


    /**
     * Api Log out
     * Invalidate the token, so user cannot use it anymore
     * They have to relogin to get a new token
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        if (!$this->guard()->check()) {
            return response([
                'message' => 'No active user session was found',
            ], 404);
        }
        $accessToken = auth()->user()->token();

        $refreshToken = DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true,
            ]);

        $accessToken->revoke();

        return response()->json(['success' => true,'data'   =>  'Session was destroyed']);
    }



}
