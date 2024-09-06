<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendSuccess($result, $message){
        $response = array(
            'success' => true,
            'data' => $result,
            'message' => $message,
        );
        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessage = [], $code = 400){
        $response = array(
            'success' => false,
            'message' => $error,
        );

        if( !empty($errorMessage) ){
            $response['errors'] = $errorMessage;
        }

        return response()->json($response, $code);
    }
}
