<?php


use App\Models\StoreToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;


function api_successWithData($data, $message)
{
    return ['status' => true, 'message' => $message, 'detail' => $data];
}
//
//function api_success($message){
//	return ['status'=>true,'message'=>$message];
//}
//
//function api_errorWithData($data,$message){
//	return ['status'=>false,'message'=>$message,'detail'=> $data];
//}
//
//function api_error($message){
//	return ['status'=>false,'message'=>$message];
//}
//
function api_validation_errors($errors, $message)
{
    return ['status' => false, 'message' => $message, 'errors' => $errors];
}


function api_success1($message)
{
    $data = array('status' => true, 'response' => array('message' => $message));
    return response()->json($data);
}

function api_success($message, $data)
{
    $data = array('status' => true, 'response' => array('message' => $message, 'data' => $data));
    return response()->json($data);
}

if (!function_exists('apiResponse')) {
    /**
     * @param boolean $status
     * @param string $msg
     * @param array|null $data
     * @param integer $http_status
     * @return \Illuminate\Http\JsonResponse
     */
    function apiResponse($status, $msg, $data = null, $http_status = 200)
    {
        return response()->json([
            'success' => $status,
            'message' => $msg,
            'data' => $data
        ], $http_status);
    }
}

if (!function_exists('getCountry')) {
    /**
     * @param boolean $status
     * @param string $msg
     * @param array|null $data
     * @param integer $http_status
     * @return \Illuminate\Http\JsonResponse
     */
    function getCountry()
    {
        $data = [];
        $details = file_get_contents('http://www.geoplugin.net/json.gp?ip=');
        $json = json_decode($details);

        $data = [
            'country_code' => $json->geoplugin_countryCode,
            'country' => $json->geoplugin_countryName,
        ];
        return (object) $data;
    }
}

function api_error($message, $httpcode = 422)
{
    $data = array('status' => false, 'error' => array('message' => $message));
    return response()->json($data, $httpcode);
}



function api_send_mail($mailInfo)
{
    try {
        $user = $mailInfo['user'];
        $mailFor = $mailInfo['mail_for'];

        if ($mailFor == 'email_verification') {
            $subject = config('app.name') . ' | Email Verification Code!';
            $template = 'emails.email-verification';
        } elseif ($mailFor == 'forgot_password') {
            $subject = config('app.name') . ' | Automatic Generated Email: Password Reset Code';
            $template = 'emails.forgot-password';
        } else {
            return 'failed to send';
        }

        $getTokenData = storeToken($user->email);

        Mail::send($template, ['code' => $getTokenData['response']['token'], 'user' => $user->first_name . ' ' . $user->last_name], function ($message) use ($user, $subject) {
            $message->from(config('mail.from.address'), config('mail.from.name'));
            $message->to($user->email, $user->first_name . ' ' . $user->last_name);
            $message->subject($subject);
        });
        return ['status' => 'success', 'response' => []];
    } catch (Exception $e) {
        return ['status' => 'error', 'response' => 'Message: ' . $e->getMessage()];
    }
}

function api_resend_code($mailInfo)
{
    try {

        $user = $mailInfo['user'];
        $mailFor = $mailInfo['mail_for'];

        if ($mailFor == 'email_verification') {
            $subject = config('app.name') . ' | Email Verification Code!';
            $template = 'emails.email-verification';
        } elseif ($mailFor == 'forgot_password') {
            $subject = config('app.name') . ' | Automatic Generated Email: Password Reset Code';
            $template = 'emails.forgot-password';
        } else {
            return 'failed to send';
        }

        $getTokenData = storeToken($user->email);

        Mail::send($template, ['code' => $getTokenData['response']['token'], 'user' => $user->first_name . ' ' . $user->last_name], function ($message) use ($user, $subject) {
            $message->from(config('mail.from.address'), config('mail.from.name'));
            $message->to($user->email, $user->first_name . ' ' . $user->last_name);
            $message->subject($subject);
        });

        return ['status' => 'success', 'response' => []];
    } catch (Exception $e) {
        return ['status' => 'error', 'response' => 'Message: ' . $e->getMessage()];
    }
}



function storeToken($source)
{
    try {
        StoreToken::where('verification_source', $source)->delete();
        $currentTime = Carbon::now();
        $token = rand(1000, 9999);
        $storeCode = new StoreToken;
        $storeCode->verification_source = $source;
        $storeCode->token = $token;
        $storeCode->expires_at = $currentTime->addMinutes(60);
        $storeCode->save();
        return ['status' => 'success', 'response' => $storeCode];
    } catch (Exception $e) {
        return ['status' => 'error', 'response' => 'Message: ' . $e->getMessage()];
    }
}
