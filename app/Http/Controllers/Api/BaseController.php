<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;


class BaseController extends Controller
{

    const LEAVING_MESSAGE = "Thank you for visiting W3Schools.com!";

    public function sendResponse($result = 'null', $message)
    {
        $response = [
            'status' => 'Success',
            'message' => $message ?? null,
            'data'    => $result,
        ];

        return response($response, 200);
    }

    function apiResponses($status, $msg, $data = null, $http_status = 200)
    {
        return response()->json([
            'success' => $status,
            'message' => $msg,
            'data' => $data
        ], $http_status);
    }

    function api_errors($data)
    {
        return response()->json(['status' => false, 'message' => $data], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
