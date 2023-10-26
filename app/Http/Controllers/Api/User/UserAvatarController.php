<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\PurchaseAvatarRequest;
use App\Http\Resources\User\userAvatarResource;
use App\Models\Avatar;
use App\Models\User;
use App\Models\UserAvatar;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserAvatarController extends Controller
{
    public function store(PurchaseAvatarRequest $request)
    {
        if (!auth('api')->check()) {
            return apiResponse(false, "PLease login first");
        }
        try {
            DB::beginTransaction();
            $avatar = Avatar::where('shortcode', $request->shortcode)->first();
            if (!$avatar) {
                return apiResponse(false, 'Avatar not found');
            }
            if (UserAvatar::where('user_id', auth()->user()->id)->where('avatar_id', $avatar->id)->first()) {
                return apiResponse(false, 'Avatar already purchased');
            }
            $userAvatar = new UserAvatar();
            $userAvatar->avatar_id = $avatar->id;
            $userAvatar->user_id = auth()->user()->id;
            $userAvatar->save();
            DB::commit();
            return apiResponse(true, 'Avatar purchased');
        } catch (Exception $e) {
            DB::rollback();
            return apiResponse(false, $e->getMessage());
        }
    }

    public function getShop()
    {
        if (!auth('api')->check()) {
            return apiResponse(false, "PLease login first");
        }
        try {
            $user = auth()->user();
            $avatars = Avatar::with(['user' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])->get();
            $avatars = userAvatarResource::collection($avatars);
            return apiResponse(true, 'Avatars', $avatars);
        } catch (Exception $e) {
            DB::rollback();
            return apiResponse(false, $e->getMessage());
        }
    }

    public function selectedAvatar(PurchaseAvatarRequest $request)
    {
        if (!auth('api')->check()) {
            return apiResponse(false, "PLease login first");
        }
        try {
            DB::beginTransaction();
            $avatar = Avatar::where('shortcode', $request->shortcode)->first();
            if (!$avatar) {
                return apiResponse(false, 'Avatar not found');
            }
            if (!UserAvatar::where('user_id', auth()->user()->id)->where('avatar_id', $avatar->id)->first()) {
                return apiResponse(false, 'Avatar not purchased!');
            }
            User::where('id', auth()->user()->id)->update(['selected_avatar' => $avatar->shortcode]);
            DB::commit();
            return apiResponse(true, 'Avatar updated');
        } catch (Exception $e) {
            DB::rollback();
            return apiResponse(false, $e->getMessage());
        }
    }
}
