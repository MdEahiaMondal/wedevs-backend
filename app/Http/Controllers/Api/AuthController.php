<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'forgotPassword', 'refresh']]);
    }

    public function register(UserRegisterRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        if ($user->save()) {
            return $this->login($request);
        }

    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }

        return $this->errorResponse('Email or Password is invalid', 401);
    }


    public function me()
    {
        return $this->showDataResponse('user', $this->guard()->user());
    }


    public function logout()
    {
        $this->guard()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }


    public function forgotPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);
        return $request->all();
    }


    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }


    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
            'user' => $this->guard()->user()
        ]);
    }


    public function guard()
    {
        return Auth::guard();
    }


    public function infoUpdate(Request $request, $slug)
    {
        $user = User::where('slug', $slug)->first();

        $this->validate($request, [
            'name' => 'required|max:100|unique:users,email,' . $request->id,
            'email' => 'required|max:80|unique:users,email,' . $request->id,
            'phone' => 'nullable|max:50',
            'permanent_address' => 'nullable',
            'present_address' => 'nullable',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return $this->showDataResponse('user_info', $user, 200, 'User info updated success');
    }


    public function passwordUpdate(Request $request, $slug)
    {
        $this->validate($request, [
            'current_password' => 'required',
            'password' => 'required|confirmed',
        ]);

        $user = User::where('slug', $slug)->first();

        if ($user->id === Auth::id()) {
            if (Hash::check($request->current_password, Auth::user()->password)) {
                if (!Hash::check($request->password, Auth::user()->password)) {
                    $user->password = Hash::make($request->password);
                    $user->save();
                    return $this->successResponse('Successfully password changed', 200);
                } else {
                    return $this->errorResponse('Current password and new password is same..', 208);
                }
            } else {
                return $this->errorResponse('Current password doesnt matched ', 404);
            }
        }

    }
}
