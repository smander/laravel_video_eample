<?php

namespace App\Services\Auth;
use Illuminate\Support\Facades\Log;
use Validator, DB, Hash, Mail;
use Illuminate\Http\Request;
use Exception;

class AuthRequests{

    public function login($email = null,$password = null){

        try{

            //Generate new login
            $client = DB::table('oauth_clients')
                ->where('password_client', true)
                ->first();

            $data = [
                'grant_type'    =>  'password',
                'client_id'     =>  $client->id,
                'client_secret' =>  $client->secret,
                'username'      =>  $email,
                'password'      =>  $password,
                'scope'         => '',
            ];


            $request_param = Request::create('/oauth/token', 'POST', $data);

            return json_decode(app()->handle($request_param)->getContent());

        } catch (Exception $e) {
            Log::critical('error due auth');
        }


    }

}
