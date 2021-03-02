<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;


class ResetPasswordController extends Controller
{
    use ApiResponse,ResetsPasswords;

    protected function sendResetResponse(Request $request, $response)
    {
        return $this->successResponse('password successfully reset. Please login');
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        $data = [
            'email' => 'Email is invalid'
        ];
        return $this->errorResponse($data, 422);
    }
}
