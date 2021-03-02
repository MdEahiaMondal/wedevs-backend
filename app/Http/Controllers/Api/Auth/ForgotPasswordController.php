<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


class ForgotPasswordController extends Controller
{
    use ApiResponse, SendsPasswordResetEmails;

    protected function validateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_url' => 'required',
        ]);

        Session::put('reset_url', $request->reset_url);
    }

    protected function credentials(Request $request)
    {
        return $request->only('email');
    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        return $this->successResponse('You are  received a password reset request. Please check your email');
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        $data = [
            'email'=> 'Something is wrong with your email'
        ];
        return $this->errorResponse($data, 422);
    }

}
