<?php

namespace App\Repositories;

use App\Models\User;
use App\Interfaces\UserInterface;
use App\Models\Scores;

class UserRepository implements UserInterface
{

    protected $model;
    protected $newModel;

    public function __construct(User $model)
    {
        $this->model = $model;
        $this->newModel = new Scores;

        return $this;
    }

    public function getAllUsers()
    {
        try {
            return $this->model->all();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getUserById($userId)
    {
        try {

            return $this->model->findOrFail($userId);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteUser($userId)
    {
        try {
            $this->model->destroy($userId);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function createUser(array $userDetails)
    {
        try {
            return $this->model->create($userDetails);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateUser($userId, array $newDetails)
    {
        try {
            return $this->model->whereId($userId)->update($newDetails);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateAndCreate($userId, $newDetails)
    {

        try {
            $data = [
                'score' => $newDetails->score,
                'rank' => $newDetails->rank,
                'name' => auth()->user()->name,
                'country_code' => auth()->user()->country_code
            ];
            return $this->newModel->updateOrCreate(['user_id' => $userId], $data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
