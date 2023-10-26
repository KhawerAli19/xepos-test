<?php

namespace App\Http\Controllers\Api\User;

use App\Isop\Isop;
use Carbon\Carbon;
use App\Models\City;
use App\Models\Song;
use App\Models\User;
use App\Models\Admin;
use App\Models\Badge;
use App\Models\Avatar;
use App\Models\Scores;
use App\Models\Sticker;
use App\Models\Category;
use App\Models\Follower;
use App\Models\UserCoin;
use App\Models\ContactUs;
use App\Models\UserBadge;
use App\Models\UserGroup;
use App\Models\StoreToken;
use App\Models\UserAvatar;
use App\Models\UserProfile;
use Illuminate\Support\Arr;
use App\Imports\ImportUsers;
use App\Models\BusinessType;
use App\Models\UserBusiness;
use Illuminate\Http\Request;
use App\Facades\CustomFacade;
use Illuminate\Http\Response;
use App\Models\BusinessSticker;
use App\Interfaces\UserInterface;
use App\Core\Traits\ImageOptimize;
use App\Notifications\AdminNotify;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Interfaces\UserAuthInterface;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\SplashResource;
use App\Notifications\PushNotification;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\SignupRequest;
use App\Http\Requests\User\SendOtpRequest;
use App\Http\Resources\LeaderBoardResource;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\User\CreateBadgeRequest;
use App\Http\Requests\User\EditProfileRequest;
use App\Http\Requests\User\VerifyTokenRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\User\CheckUsernameRequest;
use App\Http\Requests\User\CreateProfileRequest;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\ContactUs\ContactUsRequest;
use App\Http\Requests\User\ForgotPasswordRequest;
use App\Http\Requests\User\ResendVerifyTokenRequest;
use App\Http\Requests\User\UpdateProfileInfoRequest;

class AuthController extends BaseController


{



    private UserInterface $userRepository;
    private UserAuthInterface $userAuthRepository;
    protected $msg;
    use ImageOptimize;

    public function __construct(UserInterface $userRepository, UserAuthInterface $userAuthRepository)
    {
        $this->userRepository = $userRepository;
        $this->userAuthRepository = $userAuthRepository;
        $this->msg = explode('@', Route::getCurrentRoute()->getActionName())[1];
    }

    public function signup(SignupRequest $request)
    {
        try {
            $avatar = Avatar::first();
            $data = new User;
            //Api changes signup with only email and password 26/04/2023
            $data->name = $request->name;
            // $data->phone_no = $request->phone_no;
            $data->email = $request->email;
            $data->selected_avatar = $avatar->shortcode;
            $data->password = Hash::make($request->password);
            $country = getCountry();
            $data->country = $country->country;
            $data->country_code = $country->country_code;
            // $clientIP = \Request::getClientIp(true);
            $data->save();
            UserAvatar::create(['avatar_id' => $avatar->id, 'user_id' => $data->id]);
            UserCoin::create(['coins' => 20000, 'user_id' => $data->id]);
            return apiResponse(true, "User Created Successfully.");
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage());
        }
    }

    public function userDelete(Request $request)
    {
        try {
            $data = auth()->user();
            if ($data) {
                $data->status = 2;
                $data->save();
                $data->delete();
                return apiResponse(true, "account deleted");
            }
        } catch (\Exception $e) {
            return apiResponse(false, $e->getMessage());
        }
    }

    public function sendOtp(SendOtpRequest $request)
    {
        $data = User::whereEmail($request->email)->first();
        if (empty($data)) {
            return apiResponse(false, 'Email does not exists.');
        }
        $mailInfo = [
            'user' => $data,
            'mail_for' => 'email_verification'
        ];
        return apiResponse(true, 'Otp sent successfully.', $mailInfo);
    }

    public function verifyOtp(VerifyTokenRequest $request)
    {
        // check if email exists
        $data = User::whereEmail($request->email)->first();
        if (empty($data)) {
            return apiResponse(false, "Email does not exists.");
        }

        if ($result = StoreToken::where('verification_source', $request->email)->where('token', $request->otp)->first()) {

            // check if code is not expires
            if (Carbon::now() > $result->expires_at) {
                return apiResponse(false, "Otp expired.");
            }
            StoreToken::where('verification_source', $request->email)->delete();

            // update user status to active
            $data->status = 1;
            $data->save();
            return apiResponse(true, "Otp is valid");
        } else {
            return apiResponse(false, "Invalid code!");
        }
    }

    public function verifySignup(Request $request)
    {
        $data = User::whereEmail($request->email)->whereNull('deleted_at')->first();
        if (empty($data)) {
            return apiResponse(false, "Email does not exists.");
        }
        if (!empty($data)) {
            $data->email_verified_at = Carbon::now();
            $data->status = 1;
            $data->save();
            return apiResponse(true, "User Verified.");
        } else {
            return apiResponse(true, "Invalid email address.");
        }
    }

    public function resendVerificationCode(ResendVerifyTokenRequest $request)
    {
        $email = $request->email;
        $data = User::whereEmail($email)->first();
        if (!empty($data)) {
            $mailInfo = [
                'user' => $data,
                'mail_for' => 'email_verification'
            ];
            return apiResponse(true, 'Verification code sent successfullys.', $mailInfo);
        } else {
            return apiResponse(false, 'Invalid email address.');
        }
    }



    public function SocialLogin(Request $request)
    {
        try {
            $credentials = request(['email', 'id']);
            $user = User::where('email', $request->email)->where('password', $request->id)->first();
            if (!$user) {
                $user = new User;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = $request->id;
                // $user->country = getCountry();
                $country = getCountry();
                $user->country = $country->country;
                $user->country_code = $country->country_code;
                $user->save();
            }
            foreach ($user->tokens as $token) {
                $token->revoke();
            }
            Auth::loginUsingId($user->id);
            $user->device_token = request('device_token');
            $user->device_type = request('device_type');
            $user->save();
            $tokenObj = $user->createToken('user access token');
            $token = $tokenObj->token;
            $token->device_token = request('device_token');
            $token->device_type = request('device_type');
            $token->expires_at = Carbon::now()->addWeeks(4);
            $token->save();
            $token->accessToken;
            $token = $tokenObj->accessToken;
            $user->makeHidden('tokens');
            $data = Arr::add($user->toArray(), 'token_detail', ['access_token' => $token, 'token_type' => 'Bearer',]);
            return apiResponse(true, 'Login Successfully', $data);
        } catch (\Exception $ex) {
            return apiResponse(false, $ex->getMessage());
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {

        $data = User::whereEmail($request->email)->first();
        if (!empty($data)) {
            $mailInfo = [
                'user' => $data,
                'mail_for' => 'forgot_password'
            ];
            $res = api_send_mail($mailInfo); // Note: Credential required
            if ($res['status'] == 'success') {
                $get_token = StoreToken::where('verification_source',  $data->email)->first();
                return apiResponse(true, 'Verification code sent successfully.', $get_token->token);
            }
            return apiResponse(false, 'Verification code does not sent successfully.', $res);
        } else {
            return api_error('Email does not exists.');
        }
    }


    public function verifyToken(Request $request)
    {
        try {
            // return $request->email;
            $data = User::where('email', $request->email)
                // ->where('code', $request->code)
                ->first();
            // return $data;
            if (!empty($data)) {
                $verify = StoreToken::where('verification_source', $data->email)->where('token', $request->code)->first();
                if (!empty($verify)) {
                    return apiResponse(true, 'User Verified.');
                } else {
                    return apiResponse(false, 'Invalid Code.');
                }
            } else {
                return apiResponse(false, 'Invalid Email.');
            }
        } catch (\Exception $ex) {
            return apiResponse(false, $ex->getMessage());
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $verify = StoreToken::where('token', $request->code)->first();
        if (!empty($verify)) {
            $data = User::whereEmail($verify->verification_source)->first();
            if (!empty($data)) {
                $data->password = Hash::make($request->password);
                $data->save();
                StoreToken::where('verification_source', $data->email)->delete();
                return api_success1("Password changed successfully!");
            } else {
                return api_error("Invalid Code.");
            }
        } else {
            return api_error("Invalid Code.");
        }
    }


    public function updateLevel(Request $request)
    {
        if (!auth('api')->check()) {
            return apiResponse(false, "PLease login first");
        }
        try {
            $user = auth()->user();
            $user->name = $request->name;
            $user->phone_no = $request->phone_no;
            if ($request->has('image')) {
                $path = storage_path('app/public/user/image/' . $user->image);
                if (file_exists($path)) {
                    unlink($path);
                }
                $fileName = time() . '_' . $request->image->getClientOriginalName();
                $filePathAvatar = $request->file('image')->storeAs('user/image', $fileName, 'public');
                $user->image = $fileName;
            }
            if ($user->save()) {
                $score = Scores::where('user_id', $user->id)->first();
                if ($score) {
                    $score =  $score;
                } else {
                    $score = new Scores();
                }
                $score->user_id = auth()->user()->id;
                $score->level = $request->level;
                $score->rank = $request->rank;
                $score->name = auth()->user()->name;
                if ($score->save()) {
                    return apiResponse(true, 'User Level Updated Successfully', $score);
                }
                return apiResponse(false, 'User Level not Updated Successfully');
            }
        } catch (\Exception $ex) {
            return apiResponse(false, $ex->getMessage());
        }
    }

    public function splash()
    {

        $user = User::where('id', auth()->user()->id)->with('leaderBoard')->first();
        if ($user) {
            return apiResponse(true, $this->msg, new SplashResource($user));
        }
        return apiResponse(false, "splash not found");
    }

    public function leaderboard()
    {
        if (!auth('api')->check()) {
            return apiResponse(false, "PLease login first");
        }

        if (request('type') == 'local') {
            $country = getCountry();
            $countryName = $country->country;

            $user = Scores::whereIn('user_id', function ($user) use ($countryName) {
                $user->select('id')->from('users')->where('country', $countryName);
            })->orderBy('score', 'DESC')->limit(10)->get();
        } else {
            $user = Scores::orderBy('score', 'DESC')->limit(10)->get();
        }
        $myRec = Scores::where('user_id', auth()->user()->id)->first();

        $myRec = new LeaderBoardResource($myRec);
        $users = LeaderBoardResource::collection($user);
        $data = collect(['users' => $users, 'myRecord' => $myRec]);
        // dd($data);
        if ($data->count() > 0) {
            return apiResponse(true, "leaderboard", $data);
        }
        return apiResponse(false, "leaderboard data not found");
    }

    public function leaderboardPost(Request $request)
    {

        try {
            return $this->apiResponses(true, $this->msg,  $this->userRepository->updateAndCreate(auth()->user()->id, $request));
        } catch (\Exception $e) {
            return $this->api_errors($e->getMessage());
        }
    }
    public function login(Request $request)
    {
        try {
            return $this->apiResponses(true, $this->msg,  $this->userAuthRepository->login($request));
        } catch (\Exception $e) {
            return $this->api_errors($e->getMessage());
        }
    }

    public function getProfile()
    {

        try {
            return $this->apiResponses(true, "Profile Details", new UserResource($this->userRepository->getUserById(auth()->user()->id)));
            // return $this->apiResponses(true, $this->msg, $this->userRepository->getUserById(auth()->user()->id));
        } catch (\Exception $e) {
            return $this->api_errors($e->getMessage());
        }
    }

    public function bubbleSort($arr)
    {
        $n = count($arr);
        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = 0; $j < $n - $i - 1; $j++) {
                if ($arr[$j] > $arr[$j + 1]) {
                    // Swap arr[$j] and arr[$j + 1]
                    $temp = $arr[$j];
                    $arr[$j] = $arr[$j + 1];
                    $arr[$j + 1] = $temp;
                }
            }
        }
        return $arr;
    }

    public function insertionSort($arr)
    {
        $n = count($arr);
        for ($i = 1; $i < $n; $i++) {
            $key = $arr[$i];
            $j = $i - 1;
            while ($j >= 0 && $arr[$j] > $key) {
                $arr[$j + 1] = $arr[$j];
                $j = $j - 1;
            }
            $arr[$j + 1] = $key;
        }
        return $arr;
    }

    public function selectionSort($arr)
    {
        $n = count($arr);
        for ($i = 0; $i < $n - 1; $i++) {
            $minIndex = $i;
            for ($j = $i + 1; $j < $n; $j++) {
                if ($arr[$j] < $arr[$minIndex]) {
                    $minIndex = $j;
                }
            }
            // Swap the found minimum element with the first element
            $temp = $arr[$minIndex];
            $arr[$minIndex] = $arr[$i];
            $arr[$i] = $temp;
        }
        return $arr;
    }

    public function quickSort($arr)
    {
        if (count($arr) <= 1) {
            return $arr;
        }

        $pivot = $arr[0];
        $left = $right = [];

        for ($i = 1; $i < count($arr); $i++) {
            if ($arr[$i] < $pivot) {
                $left[] = $arr[$i];
            } else {
                $right[] = $arr[$i];
            }
        }

        return array_merge($this->quickSort($left), [$pivot], $this->quickSort($right));
    }

    public function heapify(&$arr, $n, $i)
    {
        $largest = $i;
        $left = 2 * $i + 1;
        $right = 2 * $i + 2;

        if ($left < $n && $arr[$left] > $arr[$largest]) {
            $largest = $left;
        }

        if ($right < $n && $arr[$right] > $arr[$largest]) {
            $largest = $right;
        }

        if ($largest != $i) {
            $temp = $arr[$i];
            $arr[$i] = $arr[$largest];
            $arr[$largest] = $temp;

            $this->heapify($arr, $n, $largest);
        }
    }

    public function heapSort(&$arr)
    {
        $n = count($arr);

        // Build max heap
        for ($i = intval($n / 2) - 1; $i >= 0; $i--) {
            $this->heapify($arr, $n, $i);
        }

        // Extract elements from heap one by one
        for ($i = $n - 1; $i > 0; $i--) {
            $temp = $arr[0];
            $arr[0] = $arr[$i];
            $arr[$i] = $temp;

            $this->heapify($arr, $i, 0);
        }
    }

    function merge($left, $right)
    {
        $result = [];
        $leftIndex = 0;
        $rightIndex = 0;

        while ($leftIndex < count($left) && $rightIndex < count($right)) {
            if ($left[$leftIndex] < $right[$rightIndex]) {
                $result[] = $left[$leftIndex];
                $leftIndex++;
            } else {
                $result[] = $right[$rightIndex];
                $rightIndex++;
            }
        }

        // Add remaining elements from both arrays
        while ($leftIndex < count($left)) {
            $result[] = $left[$leftIndex];
            $leftIndex++;
        }

        while ($rightIndex < count($right)) {
            $result[] = $right[$rightIndex];
            $rightIndex++;
        }

        return $result;
    }

    function mergeSort($arr)
    {
        if (count($arr) <= 1) {
            return $arr;
        }

        $mid = count($arr) / 2;
        $left = $this->mergeSort(array_slice($arr, 0, $mid));
        $right = $this->mergeSort(array_slice($arr, $mid));

        return $this->merge($left, $right);
    }

    function binaryToDecimal($binaryNumber)
    {
        if (!is_numeric($binaryNumber)) {
            throw new \Exception('Please pass a valid Binary Number for Converting it to a Decimal Number.');
        }

        $decimalNumber = 0;
        $binaryDigits  = array_reverse(str_split($binaryNumber));


        foreach ($binaryDigits as $index => $digit) {
            $decimalNumber += $digit * pow(2, $index);
        }

        return $decimalNumber;
    }
    function countSentences(string $sentence): int
    {
        $sentence = trim($sentence);

        return preg_match_all('/[^\s|^\...](\.|\!|\?)(?!\w)/', $sentence);
    }
    function reverseString(string $string)
    {
        $string = trim($string); // Removing leading and trailing spaces

        $characters = str_split($string);

        $reversedCharacters = [];

        for ($i = (count($characters) - 1); $i >= 0; $i--) {
            $reversedCharacters[] = $characters[$i];
        }

        return implode('', $reversedCharacters);
    }

    public function upload()
    {

        // custom facades
        $foo = new Isop();
        return $foo->Payment();



        // Example usage
        $reviews = [
            "This product is amazing! Best purchase ever!",
            "This vendor is very amazing! Best purchase ever!",
            "Worst product I've ever bought. Complete disappointment.",
            "This is a short review.",
            "A++++++! Fantastic product!!!",
            "1111 Terrible purchase 1111"
        ];

        foreach ($reviews as $review) {
            if ($this->isFakeReview($review)) {
                echo "Fake Review: $review\n";
            } else {
                echo "Genuine Review: $review\n";
            }
        }


        //config
        // $namefile = storage_path("test.html");
        // $content = "hello";

        // //save file
        // $file = fopen($namefile, "w") or die("Unable to open file!");
        // fwrite($file, $content);
        // fclose($file);

        // //header download
        // header("Content-Disposition: attachment; filename=\"" . $namefile . "\"");
        // header("Content-Type: application/force-download");
        // header('Expires: 0');
        // header('Cache-Control: must-revalidate');
        // header('Pragma: public');
        // header("Content-Type: text/plain");

        // echo $content;

        // $time_start = microtime(true);

        // $numbers = [64, 34, 25, 12, 22, 1100, 901];
        // $bubbleSort = $this->bubbleSort($numbers);
        // $insertionSort = $this->insertionSort($numbers);
        // $selectionSort = $this->selectionSort($numbers);
        // $quickSort = $this->quickSort($numbers);
        // $this->heapSort($numbers);
        // $mergeSort = $this->mergeSort($numbers);


        // echo "Sorted array bubbleSort: " . implode(", ", $bubbleSort) . " \n ";
        // echo "Sorted array insertionSort: " . implode(", ", $insertionSort) . " \n ";
        // echo "Sorted array selectionSort: " . implode(", ", $selectionSort) . " \n ";
        // echo "Sorted array quickSort: " . implode(", ", $quickSort) . " \n ";
        // echo "Sorted array heapSort: " . implode(", ", $numbers) . " \n ";
        // echo "Sorted array mergeSort: " . implode(", ", $mergeSort) . " \n ";

        // return $this->binaryToDecimal(111);
        // return $this->countSentences($sentence);
        // $sentence = "Sorted array bubbleSort. hello w";

        // return $this->reverseString($sentence);
    }

    function isFakeReview($review)
    {
        // Rule 1: Check for excessively positive language
        $positiveWords = ['amazing', 'best', 'incredible', 'fantastic', 'awesome'];
        $positiveCount = 0;
        foreach ($positiveWords as $word) {
            if (stripos($review, $word) !== false) {
                $positiveCount++;
            }
        }

        // Rule 2: Check for overly negative language
        $negativeWords = ['terrible', 'awful', 'horrible', 'worst', 'disappointing'];
        $negativeCount = 0;
        foreach ($negativeWords as $word) {
            if (stripos($review, $word) !== false) {
                $negativeCount++;
            }
        }

        // Check for excessive exclamation marks
        $exclamationMarksCount = preg_match_all("/!{2,}/", $review);

        // Check for repeated characters (e.g., "loooove", "greatttt")
        $repeatedCharactersCount = preg_match_all("/(.)\\1{2,}/", $review);


        // Rule 3: Check for excessive capitalization
        $capitalLettersCount = preg_match_all("/[A-Z]/", $review);
        $capitalLettersThreshold = 10;

        // Rule 4: Check for short reviews
        $minReviewLength = 10;

        // Rule 5: Check for unusual review patterns
        $unusualPatterns = ['1111', '!!!!', '********'];
        $unusualPatternCount = 0;
        foreach ($unusualPatterns as $pattern) {
            if (stripos($review, $pattern) !== false) {
                $unusualPatternCount++;
            }
        }

        // Set thresholds for each rule
        $positiveThreshold = 2; // Number of positive words required to flag a review as potentially fake
        $negativeThreshold = 2; // Number of negative words required to flag a review as potentially fake
        $unusualPatternThreshold = 1; // Number of unusual patterns required to flag a review as potentially fake
        $exclamationMarksThreshold = 2;
        $repeatedCharactersThreshold = 3;

        // Check if the review meets any of the fake review criteria
        if (
            $positiveCount >= $positiveThreshold ||
            $negativeCount >= $negativeThreshold ||
            $capitalLettersCount >= $capitalLettersThreshold ||
            strlen($review) < $minReviewLength ||
            $unusualPatternCount >= $unusualPatternThreshold ||
            $exclamationMarksCount > $exclamationMarksThreshold ||
            $repeatedCharactersCount > $repeatedCharactersThreshold
        ) {
            return true; // It's a potential fake review
        }

        return false; // It's likely a genuine review
    }



    public function uploads(Request $request)
    {


        // Excel::import(new Song, request()->file('file'));
        // return apiResponse(true, "Upload successfully!");
        // return back();

        Excel::import(new ImportUsers, request()->file('file'));
        return apiResponse(true, "Upload successfully!");
        // return Excel::download(new Song, 'users.xlsx');
        // return response()->stream($callback, 200, $headers);

        $type = 'csv';

        $data = Song::get()->toArray();
        return Excel::create('laravelcode', function ($excel) use ($data) {
            $excel->sheet('mySheet', function ($sheet) use ($data) {
                $sheet->fromArray($data);
            });
        })->download($type);


        if ($request->has('songs')) {

            foreach ($request->file('songs') as $song) {
                if (!Song::where('name', explode('.', $song->getClientOriginalName())[0])->exists()) {
                    $filename = str_replace(' ', '_', $song->getClientOriginalName());
                    $song->move(public_path('music/'), $filename);
                    $getID3 = new \getID3();
                    $duration = $getID3->analyze(public_path('music/') . $filename)['playtime_string'];
                    $data = [
                        'name' => explode('.', $song->getClientOriginalName())[0],
                        'filename' => $filename,
                        'length' => $duration
                    ];
                    Song::updateOrCreate(['name' => $filename], $data);
                }
            }
            return apiResponse(true, "Upload successfully!");
        }
    }

    public function editProfile(EditProfileRequest $request)
    {

        if (!auth('api')->check()) {
            return apiResponse(false, 'PLease login first');
        }

        try {
            $user = auth()->user();
            $user->name = $request->name;
            $user->phone_no = $request->phone_no;
            $user->city = $request->city;
            $user->state = $request->state;
            $user->country = $request->country;

            $newImage = $request->file('image');
            if ($newImage) {
                $user->clearMediaCollection();
                $user->addMedia($newImage)
                    ->toMediaCollection();
            }

            // if ($request->has('image')) {
            // return $this->optimizeable($request);
            // $path = storage_path('app/public/user/image/' . $user->image);
            // if (file_exists($path)) {
            //     unlink($path);
            // }
            // $fileName = time() . '_' . $request->image->getClientOriginalName();
            // $filePathimage = $request->file('image')->storeAs('user/image', $fileName, 'public');
            //     $user->image = $this->optimizeable($request);
            // }
            if ($user->save()) {
                return apiResponse(true, "Profile updated successfully", new UserResource($user));
                // return apiResponse(true, "Profile updated successfully", $user);
            }
        } catch (\Exception $ex) {
            return apiResponse(false, $ex->getMessage());
        }
    }

    public function logout()
    {
        try {
            $user = auth('api')->user();
            $user->token()->revoke();
            return apiResponse(true, "Logout successfully!");
        } catch (\Exception $ex) {
            return apiResponse(false, $ex->getMessage());
        }
    }

    public function contactUs(ContactUsRequest $request)
    {
        try {
            $data = ContactUs::create($request->all());
            if ($data) {
                return apiResponse(true, "Successfully Send", $data);
            }
            return apiResponse(false, "Not Send");
        } catch (\Exception $ex) {
            return apiResponse(false, $ex->getMessage());
        }
    }


    public function changePassword(Request $request)
    {
        if (Hash::check($request->current_password, auth('api')->user()->password)) {
            $user = auth('api')->user();
            $user->password =  bcrypt($request->new_password);
            $user->save();
            return apiResponse(true, 'Password has been updated successfully');
        }
        return apiResponse(false, 'current password in not valid');
    }

    public function getUserProfile(User $user)
    {
        $user = $user->load('user_badges');
        $data['id'] = $user->id;
        $data['name'] = $user->name;
        $data['username'] = $user->username;
        $data['email'] = $user->email;
        $data['avatar'] = $user->avatar;
        $data['bio'] = $user->bio;
        $data['following'] = $user->IFollow($user->id);
        $data['follower'] = $user->isFollowMe($user->id);
        $data['user_badges'] = $user->user_badges;
        $data['badges_count'] = count($user->user_badges);


        return api_success('User Profile', $data);
    }



    public function sendError($errorMessages, $errors = null, $code = 422)
    {
        $response = [
            'status' => 422,
            'message' => $errorMessages,
            'data' => $errors,

        ];

        return response($response, $code);
    }
}
