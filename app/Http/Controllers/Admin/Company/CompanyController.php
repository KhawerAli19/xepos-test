<?php

namespace App\Http\Controllers\Admin\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Company::when(request()->filled('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            });
        })->paginate(request('entries'));
        return response()->json(compact('company'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $data = new Company();
        $data->name = $request->name;
        $data->email = $request->email;
        $data->website = $request->website;
        if ($request->has('logo')) {
            $fileName = time() . '_' . $request->logo->getClientOriginalName();
            $filePathAvatar = $request->file('logo')->storeAs('company/logo', $fileName, 'public');
            $data->logo = $fileName;
        }
        if ($data->save()) {
            return response()->json(['status' => true, 'msg' => 'Company Added Successfully']);
        }

        return response()->json(['status' => true, 'msg' => 'Something Error']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = Company::find($id);
        return response()->json(compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $company = Company::where('id', $id)->update($request->all());
        return response()->json(['status' => true, 'msg' => 'Company Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Company::where('id', $id)->delete();
        return response()->json(['status' => true, 'msg' => 'Company Deleted Successfully']);
    }
}
