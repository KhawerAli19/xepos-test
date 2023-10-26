<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAvatar extends Model
{
    use HasFactory;

    protected $fillable = ['avatar_id', 'user_id'];

    // public function user()
    // {
    //     $this->hasMany(User::class);
    // }

    // public function avatar()
    // {
    //     $this->hasMany(Avatar::class);
    // }
}
