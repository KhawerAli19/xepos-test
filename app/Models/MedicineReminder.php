<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineReminder extends Model
{
    use HasFactory;

    protected $table = 'medicine_reminder';

    protected $fillable = ['medicine_id', 'interval', 'duration', 'duration_number', 'remind_me', 'status', 'user_id'];

    protected $appends = ['type'];


    public function time_schedule()
    {
        return $this->hasMany(TimeSchedule::class, 'reminder_id');
    }

    public function reminder()
    {
        return $this->morphOne('App\Models\ReminderLogs', 'reminderable');
    }


    public function getTypeAttribute()
    {
        return $this->type = 'Medicine Reminder';
    }
}
