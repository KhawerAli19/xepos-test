<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $fillable = ['name', 'filename', 'length'];
    protected $table = 'songs';
}
