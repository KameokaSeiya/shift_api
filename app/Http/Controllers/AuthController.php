<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    #新規登録
    public function register(Request $request) {
        $user=User::create([
            'name'=>$request->name,
            'employee_number'=>$request->employee_number,
            'password'=>Hash::make($request->password),
        ]);
        $json =[
            'data'=>$user
        ];
        return response()->json($json,Response::HTTP_OK);
    }


    #ログイン
    public function login(Request $request){
        if (Auth::attempt(['employee_number'=>$request->employee_number,'password'=>$request->password])){
            $user=User::whereEmployeeNumber($request->employee_number)->first();
            $user->tokens()->delete();
            $token=$user->createToken("login:user{$user->id}")->plainTextToken;
            #ログインが成功したらトークンを返す
            return response()->json(['token'=>$token],Response::HTTP_OK);
        }
        return response()->json('ログイン失敗しました。',Response::HTTP_INTERNAL_SERVER_ERROR);
    }

}
