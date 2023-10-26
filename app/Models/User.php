<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

// class User extends Authenticatable implements HasMedia
class User extends Authenticatable implements HasMedia

{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];
    protected $fillable = ['name', 'email', 'password', 'phone_no', 'image', 'country', 'city', 'state', 'status', 'device_token', 'device_type', 'selected_avatar'];
    protected $appends = ['image_url', 'token', 'coins', 'profile_photo_url'];
    /**
     * The attributes that should be hidden for serialization.
     *

     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $statuses = [
        0 => 'Inactive',
        1 => 'Active',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getCoinsAttribute()
    {
        return $this->coin->coins ?? null;
        //OR build 'roles_list' manually, returning whatever you like.
    }

    public function coin()
    {
        return $this->hasOne(UserCoin::class, 'user_id', 'id');
    }


    public function getImageUrlAttribute()
    {
        return asset("/storage/user/image/{$this->image}", false);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->sharpen(10);
    }

    protected function getProfilePhotoUrlAttribute()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=ffffff&background=9333ea';
    }



    // public function storeAvatar()
    // {
    //     if (request()->hasFile('image')) {
    //         $file = storage_path('public/media' . $this->image);
    //         if (is_file($file) && file_exists($file)) {
    //             unlink($file);
    //         }
    //         $path = $file->store('public/media');
    //         $this->avatar = basename($path);
    //     }
    // }



    public function getTokenAttribute()
    {
        return encrypt($this->id);
    }

    public function leaderBoard()
    {
        return $this->hasOne(Scores::class, 'user_id', 'id');
    }

    public function avatar()
    {
        return $this->belongsToMany(Avatar::class, 'user_avatars');
    }
}
