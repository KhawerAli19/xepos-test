<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoggingReminder extends Model
{
    use HasFactory;



    protected $table = 'jogging_reminder';

    protected $fillable = ['user_id','jogging_time','interval','duration_type','duration_number','remindTime','remindWithAlarm','status'];
   
    protected $appends = ['type'];

    public function days()
    {
        return $this->hasMany(JoggingDays::class,'jogging_id');
    }

    public function reminder()
    {
        return $this->morphOne('App\Models\ReminderLogs', 'reminderable');
    }


    public function getTypeAttribute(){
        return $this->type = 'Jogging Reminder';
    }
}
