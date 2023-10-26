<?php

namespace App\Http\Controllers\Api\Reminders;

use DateTime;
use App\Models\User;
use App\Models\Admin;
use App\Models\Medicine;
use App\Models\ReminderLogs;
use App\Models\TimeSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\JoggingReminder;
use App\Models\StretchReminder;
use App\Models\DrinkingReminder;
use App\Models\MedicineReminder;
use App\Notifications\AdminNotify;
use Illuminate\Support\Facades\DB;
use App\Models\AppointmentReminder;
use App\Http\Controllers\Controller;
use App\Core\Notifications\PushNotification;
use Illuminate\Support\Facades\Notification;

class MedicineReminderController extends Controller
{

    public function getMedicineNotification()
    {
        $now = Carbon::now()->format('H:i');
        // $now = '22:00';

        $medicine = MedicineReminder::with('time_schedule')->where('status', 1)->get();
        foreach ($medicine as $get_time) {
            $user = User::where('id', $get_time->user_id)->first();
            foreach ($get_time->time_schedule as $time) {
                $new_time = DateTime::createFromFormat('h:i A', $time->time_schedule);
                $alertTime = $new_time->format('H:i');
                return $alertTime;
                if ($alertTime == $now) {
                    $data['data'] = [
                        'id' =>  $get_time->medicine_id,
                        'type' => 'medicine',
                        'alarm' => true,
                        'title' => 'medicine',
                    ];
                    $user->notify(new PushNotification(
                        'medicine',
                        'medicine',
                        $data
                    ));
                }
            }
        }

        return $medicine;
    }

    public function getStretchNotification()
    {
        // $now = Carbon::now();
        $stretch = StretchReminder::where('status', 1)->get();
        foreach ($stretch as $get_time) {
            // $alertTime = new Carbon($get_time->wakeup_time);
            $user = User::where('id', $get_time->user_id)->first();

            $data['data'] = [
                'id' => 1,
                'type' => 'posture',
                'alarm' => true,
                'title' => 'posture',
            ];
            $user->notify(new PushNotification(
                'posture',
                'posture',
                $data
            ));
        }
        // }
        return $data;
    }

    public function getDrinkingNotification()
    {
        $now = Carbon::now();
        $drinking = DrinkingReminder::where('status', 1)->get();
        // return $drinking;
        foreach ($drinking as $get_time) {
            // $alertTime = new Carbon($get_time->wakeup_time);
            $user = User::where('id', $get_time->user_id)->first();
            // if ($alertTime == $now) {
            $data['data'] = [
                'id' =>  $get_time->id,
                'type' => 'drinking',
                'alarm' => true,
                'title' => 'drinking',
            ];
            $user->notify(new PushNotification(
                'drinking',
                'drinking',
                $data
            ));
            // }
        }
        return $drinking;
    }

    public function getJoggingNotification()
    {
        $now = Carbon::now()->format('H:i');
        // $now = '13:00';
        $jogging = JoggingReminder::with('reminder')->where('status', 1)->get();
        foreach ($jogging as $get_time) {
            $new_time = DateTime::createFromFormat('h:i A', $get_time->jogging_time);
            $alertTime = $new_time->format('H:i');
            $user = User::where('id', $get_time->user_id)->first();
            if ($alertTime == $now) {
                $data['data'] = [
                    'id' =>  $get_time->id,
                    'type' => 'jogging',
                    'alarm' => true,
                    'title' => 'jogging',
                ];
                $user->notify(new PushNotification(
                    'jogging',
                    'jogging',
                    $data
                ));
            }
        }
        return $jogging;
    }

    public function getAppointmentNotification()
    {
        $now = Carbon::now();
        $appointment = AppointmentReminder::with('appointment_times')->where('status', 1)->get();
        foreach ($appointment as $get_time) {
            $user = User::where('id', $get_time->user_id)->first();
            foreach ($get_time->appointment_times as $time) {
                // $alertTime = new Carbon($time->reminder_time);

                // if ($alertTime == $now) {
                $data['data'] = [
                    'id' =>  $get_time->id,
                    'type' => 'appointment',
                    'alarm' => true,
                    'title' => 'appointment',
                ];
                $user->notify(new PushNotification(
                    'appointment',
                    'appointment',
                    $data
                ));
            }
        }

        return $appointment;
    }


    public function setMedicineReminder(Request $request)
    {

        if (!auth('api')->check()) {
            return api_error('Message: Login required');
        }
        try {
            $medicine = Medicine::where('id', $request->medicine_id)->first();
            if (!$medicine) {
                return api_success1('Medicine Not Found.');
            }
            $input['medicine_id'] = $request->medicine_id;
            $input['duration'] =  $request->duration;
            $input['duration_number'] =  $request->duration_number;
            $input['remind_me'] =  $request->remind_me;
            $input['user_id'] =  auth()->user()->id;
            $input['status'] =  1;

            $data = MedicineReminder::create($input);

            $log = ReminderLogs::create(['user_id' => auth()->user()->id, 'reminder_date' => Carbon::now()->format('d-m-y')]);
            $input['reminderable_id'] = $data->id;

            if ($data->reminder()->save($log)) {


                foreach ($request->time_schedule as $time) {
                    $vehicle_data[] = ['time_schedule' => $time, 'reminder_id' => $data->id,];
                }
                $data->time_schedule()->createMany($vehicle_data);
                $lastest_appointment = MedicineReminder::with('time_schedule')->latest()->first();

                $admin_notify = Admin::where('id', 1)->first();
                Notification::send($admin_notify, new AdminNotify([
                    'title' => 'New Medicine Reminder Added',
                    'message' => 'User Id: ' . $data->id,
                    'id' => $data->id,
                    'route' => 'admin.users.show',

                ]));

                return api_success('Reminder Set Successfully', $lastest_appointment);
            }
        } catch (\Exception $ex) {
            return api_error('message: ' . $ex->getMessage(), 500);
        }
    }

    public function updateMedicineReminder(Request $request)
    {

        if (!auth('api')->check()) {
            return api_error('Message: Login required');
        }

        try {

            $data =  $request->only(['medicine_id', 'interval', 'duration', 'duration_number', 'remind_me', 'status']);

            $dataSaved = MedicineReminder::where('medicine_id', $request->medicine_id)->update($data);

            $getMed = MedicineReminder::where('medicine_id', $request->medicine_id)->first();

            if ($dataSaved) {
                if (request('time_schedule')) {
                    TimeSchedule::where('reminder_id', $getMed->id)->delete();
                    foreach ($request->time_schedule as $time) {
                        $vehicle_data[] = ['time_schedule' => $time, 'reminder_id' => $getMed->id,];
                    }
                    $getMed->time_schedule()->createMany($vehicle_data);
                }
                $lastest_appointment = MedicineReminder::with('time_schedule')->where('medicine_id', $request->medicine_id)->first();

                return api_success('Reminder Updated Successfully', $lastest_appointment);
            }
        } catch (\Exception $ex) {
            return api_error('message: ' . $ex->getMessage(), 500);
        }
    }

    public function setMedicineNotification()
    {
        $search = '';
        if (request()->has('type')) {
            if (request('type') == 'medicine') {
                $search = 'App\Models\MedicineReminder';
            } else {
                return api_success1('Record Not Found.');
            }
        }

        $check = ReminderLogs::with('reminderable')
            ->when(request()->filled('type'), function ($q) use ($search) {
                $q->whereHasMorph('reminderable', $search);
            })
            ->where('reminderable_id', request('id'))
            ->where('user_id', auth()->user()->id)
            ->update(['completed' => request('completed') ?? false, 'snooze_status' => request('snooze') ?? false]);
        if ($check) {
            $getData = ReminderLogs::with('reminderable')
                ->when(request()->filled('type'), function ($q) use ($search) {
                    $q->whereHasMorph('reminderable', $search);
                })
                ->where('reminderable_id', request('id'))
                ->where('user_id', auth()->user()->id)->first();
            return api_success('Medicine Notification Set Successfully', $getData);
        }
        return api_success1('Reminder Not Found.');
    }
}
