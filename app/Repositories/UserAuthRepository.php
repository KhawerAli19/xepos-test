<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\UserAuthInterface;

class UserAuthRepository implements UserAuthInterface

{
    protected $model;


    public function __construct(User $model)
    {
        $this->model = $model;
        return $this;
    }

    public function login($data)
    {
        try {
            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                throw new \Exception('Invalid credentials.');
            }
            $user = $data->user();
            if (!$user->status) {
                throw new \Exception('Please verify your email address');
            }
            foreach ($user->tokens as $token) {
                $token->revoke();
            }
            $user->device_token = $data->device_token;
            $user->device_type = $data->device_type;
            $user->save();
            $tokenObj = $user->createToken('user access token');
            $token = $tokenObj->token;
            $token->device_token = $data->device_token;
            $token->device_type = $data->device_type;
            $token->expires_at = Carbon::now()->addWeeks(4);
            $token->save();
            $token->accessToken;
            $token = $tokenObj->accessToken;
            $user->makeHidden('tokens');
            $data = Arr::add($user->toArray(), 'token_detail', ['access_token' => $token, 'token_type' => 'Bearer',]);
            return $data;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
