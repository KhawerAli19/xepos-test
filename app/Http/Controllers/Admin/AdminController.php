<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Fact;
use App\Models\ReminderLogs;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserBusiness;
use App\Models\UserDidYouKnow;
use App\Models\UserPackages;
use App\Models\UserSmile;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function __invoke()
    {
        return view('layouts.admin');
    }
    public function home(Request $request)
    {
        $total_companies = Company::count();
        $total_employees = Employee::count();

        return response()->json(compact(
            'total_companies',
            'total_employees',
        ));
    }
}
