<?php


namespace App\Traits;


trait ApiResponse
{

    protected function successResponse($message, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message
        ], $code);
    }

    protected function showDataResponse($dataName, $data = null, $code = 200, $message = null)
    {
        return response()->json([
            'message' => $message,
            'success' => true,
            $dataName => $data
        ], $code);
    }

    protected function errorResponse($message, $code)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }
}
