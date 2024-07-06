<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Password;


class AuthController extends Controller
{
    #新規登録
    public function register(Request $request) {
        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);
        $json =[
            'data'=>$user
        ];
        return response()->json($json,Response::HTTP_OK);
    }


    #ログイン
    public function login(Request $request){
        
        if (Auth::attempt(['email'=>$request->email,'password'=>$request->password])){
            $user=User::whereEmail($request->email)->first();
            $user->tokens()->delete();
            $token=$user->createToken("login:user{$user->id}")->plainTextToken;
            #ログインが成功したらトークンを返す
            return response()->json(['token'=>$token],Response::HTTP_OK);
        }
        return response()->json('ログイン失敗しました。',Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    #ログアウト
    public function logout(Request $request):RedirectResponse{
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    #パスワードリセット
    public function resetPassword(ResetPasswordRequest $request){
        $user=User::whereEmail($request->email)->first();
        
        #ユーザーが存在しない場合404を返す
        if(!$user){
            abort(404);
        }
       
        #パスワードを保存
        $user->password=Hash::make($request->password);

        if($request->password != $request->confirmation_password){
            return back()->withErrors(['confirmation_password'=>'パスワードが一致しません']);
        }

        $user->save();
        return response()->json('パスワード変更しました。',Response::HTTP_OK);
    }
}
