<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CoinRequest;
use App\Models\UserCoin;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoinController extends Controller
{
    public function update(CoinRequest $request)
    {
        if (!auth('api')->check()) {
            return apiResponse(false, "PLease login first");
        }
        try {
            DB::beginTransaction();
            $userCoin = UserCoin::where('user_id', auth()->user()->id)->first();
            if (!$userCoin) {
                $userCoin = new UserCoin();
                $userCoin->user_id  = auth()->user()->id;
                $userCoin->coins     = $request->coins;
            } else {
                $userCoin->coins = $request->coins;
            }
            if ($userCoin->save()) {
                DB::commit();
                return apiResponse(true, 'Coins updated');
            }
            return apiResponse(false, 'Coins not update');
        } catch (Exception $e) {
            DB::rollback();
            return apiResponse(false, $e->getMessage());
        }
    }
}
