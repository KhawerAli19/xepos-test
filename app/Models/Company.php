<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'logo', 'website'];
    protected $appends = ['logo_url'];
    public function getLogoUrlAttribute()
    {
        return asset("/storage/company/logo/{$this->logo}", false);
        // return  storage_path('public/company/logo' . $this->logo);
    }
}
