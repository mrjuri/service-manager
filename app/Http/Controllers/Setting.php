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
        foreach ($request->all() as $k => $v) {

            if ($k != '_token') {

                $settings = \App\Model\Setting::where('name', $k)
                    ->get();

                if (count($settings) > 0) {

                    $setting = $settings[0];

                    $setting_up = \App\Model\Setting::find($setting->id);
                    $setting_up->value = $v;
                    $setting_up->save();

                } else {

                    $setting_ins = new \App\Model\Setting();
                    $setting_ins->name = $k;
                    $setting_ins->value = $v;
                    $setting_ins->save();
                }

            }

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
