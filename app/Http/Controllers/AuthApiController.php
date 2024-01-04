<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\tblShop;
use Auth;
use DB;

class AuthApiController extends Controller
{
    public function login(request $request)
    {
        $fields = $request->validate([
            'email'=>'required',
            'password'=>'required'
        ]);

        $uid = '';

        if(Auth::attempt(['email' => $fields['email'], 'password' => $fields['password'], 'status' => '1']))
        {
            $uid = Auth::user()->id;

            $data = User::where('id', $uid)->first();

            $token = $data->createToken('myapptoken')->plainTextToken;

            if(Auth::user()->type == 1)
            {
                $response = [
                    'user'      => $data,
                    'usertype'  => "1",
                    'status'=>true,
                    'token'     => $token
                ];

                return response($response, 200);
                exit();
            }
            if(Auth::user()->type == 2) 
            {
                $response = [
                    'user'      => $data,
                    'usertype'  => "2",
                    'status'=>true,
                    'token'     => $token,
                ];

                return response($response, 200);
                exit();
            }
            if(Auth::user()->type == 3) 
            {
                $response = [
                    'user'      => $data,
                    'usertype'  => "3",
                    'status'    => true,
                    'token'     => $token
                ];

                return response($response, 200);
                exit();
            }
            if(Auth::user()->type == 4)  
            {
                $response = [
                    'user'      => $data,
                    'usertype'  => "3",
                    'status'    => true,
                    'token'     => $token
                ];

                return response($response, 200);
                exit();
            }
        }
        else
        {
            return response([
                'message' => 'Bad Credentials'
            ], 401);
        }
    }
    public function directusersapi(request $request)
    {
        $data = tblShop::where(['agentid' => '0'])->orderBy('id', 'desc')->get();

        $count = count($data); 

        if($count>0)
        {
            $response = [
                'status' =>  true,
                'data'   =>  $data
            ];

            return response($response, 200);
        }
        else
        {
            $response = [
                'status'  => false,
                'message' => 'Sorry!! No matching data availbale now!!'
            ];

            return response($response, 401);
        }
    }

    public function logout(){
        Auth::user()->tokens()->delete();
        return response()->json([
            'message'=>'You have successfully logged out'
        ]);
    }

    public function urlapi(request $request)
    {
        $fields = $request->validate([
            'id'=>'required'
        ]);

        $count = tblShop::where(['id' => $fields['id']])->count();

        if($count>0)
        {
            $regid = tblShop::where(['id' => $fields['id']])->value('regid');
            $url = 'https://exconnectnfc.com/dc/'.$regid;

            $response = [
                'status' =>  true,
                'url'   =>  $url
            ];

            return response($response, 200);
        }
        else
        {
            $response = [
                'status'  => false,
                'message' => 'Sorry!! No matching data availbale now!!'
            ];

            return response($response, 401);
        }
    }
}
