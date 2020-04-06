<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class Setting extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    	$settings = \App\Model\Setting::get();

	    $settings_data = '';

    	if (count($settings) > 0) {

		    foreach ($settings as $setting) {

			    $settings_data_array[$setting->name] = $setting->value;

		    }

		    $settings_data = (object) $settings_data_array;

	    }

        return view('setting.form', [
        	'settings' => $settings_data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    	$now = Carbon::now();

    	$setting = \App\Model\Setting::where('name', 'email_name_sender')
	        ->get();

    	if (count($setting) > 0) {

//    		dd($setting);

	    } else {

		    foreach ($request->all() as $k => $v) {

			    if ($k != '_token') {

				    $data_array[] = array(
					    'name' => $k,
					    'value' => $v,
					    'created_at' => $now,
					    'updated_at' => $now
				    );

			    }

		    }

		    \App\Model\Setting::insert($data_array);
	    }

	    return redirect()->route('setting.create');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
