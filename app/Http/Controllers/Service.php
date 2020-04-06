<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Input;

class Service extends Controller
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
    public function index(Request $request)
    {
//        $s = Input::get('s');
        $s = $request->input('s');

        if ($s == '') {
            $request->session()->forget('ss');
        } else {
            $request->session()->put('ss', $s);
        }

        /*$services = \App\Model\Service::orderBy('name')
            ->with('details')
            ->orWhere('name', 'LIKE', '%' . $s . '%')
            ->get();*/

        $getData = new getData();
        $totals = $getData->totals();

        $services = DB::table('services')
            ->leftJoin(
                'customers_services_details',
                'customers_services_details.service_id', '=', 'services.id'
            )
            ->select([
                'services.id AS id',
                'services.name AS name',
                'services.is_share AS is_share',
                DB::raw('COUNT(customers_services_details.service_id) AS n_servizi_venduti'),
                'services.price_buy AS price_buy',
                DB::raw('IF(
                    services.is_share = 1,
                    SUM(customers_services_details.price_sell),
                    services.price_sell
                ) AS price_sell'),
                DB::raw('(IF(
                    services.is_share = 1,
                    SUM(customers_services_details.price_sell),
                    services.price_sell
                ) - services.price_buy) AS price_utile'),
                DB::raw('((IF(
                    services.is_share = 1,
                    SUM(customers_services_details.price_sell),
                    services.price_sell
                ) - services.price_buy) / services.price_buy * 100) AS per_utile'),
                DB::raw('(SUM(customers_services_details.price_sell)
                - (IF(
                    services.is_share = 1,
                    services.price_buy,
                    services.price_buy * COUNT(customers_services_details.service_id)
                ))) AS price_utile_totale'),
                DB::raw('(SUM(customers_services_details.price_sell)
                - (IF(
                    services.is_share = 1,
                    services.price_buy,
                    services.price_buy * COUNT(customers_services_details.service_id)
                ))) / ' . $totals['price_utile'] . ' * 100 AS per')
            ])
            ->orWhere('name', 'LIKE', '%' . $s . '%')
            ->groupBy('services.id')
            ->orderBy('price_utile', 'DESC')
            ->get();

        return view('service.list', [
            'services' => $services,
            's' => $s
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('service.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $service = new \App\Model\Service();
        $service->name = $request->input('name');
        $service->price_buy = floatval(str_replace(',', '.', $request->input('price_buy')));
        $service->price_sell = floatval(str_replace(',', '.', $request->input('price_sell')));
        $service->is_share = $request->input('is_share');

        $service->save();

        return redirect()->route('service.list', ['s' => $request->session()->get('ss')]);
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
        $service = \App\Model\Service::where('id', $id)
            ->get();

	    $customers = DB::table('customers AS c')
			->join(
				'customers_services_details AS csd_c',
				'csd_c.customer_id', '=', 'c.id'
			)
			->join(
				'services AS s',
				's.id', '=', 'csd_c.service_id'
			)
			->select([
				'c.id AS id',
				'c.company AS company',
				'c.name AS name',
				'c.email AS email',
				DB::raw('SUM(csd_c.price_sell) AS price_sell'),
				DB::raw('SUM(IF(
					s.is_share = 1,
					s.price_buy / (
						SELECT COUNT(id) AS count FROM customers_services_details
							WHERE service_id = s.id
							GROUP BY s.id
					),
					s.price_buy)) AS price_buy'),
				DB::raw('(SUM(csd_c.price_sell) - SUM(IF(
					s.is_share = 1,
					s.price_buy / (
						SELECT COUNT(id) AS count FROM customers_services_details
							WHERE service_id = s.id
							GROUP BY s.id
					),
					s.price_buy))) AS price_utile'),
				DB::raw('((SUM(csd_c.price_sell) - SUM(IF(
                    s.is_share = 1,
                    s.price_buy / (
                        SELECT COUNT(id) AS count FROM customers_services_details
                            WHERE service_id = s.id
                            GROUP BY s.id
                    ),
                    s.price_buy
                ))) / SUM(IF(
                    s.is_share = 1,
                    s.price_buy / (
                        SELECT COUNT(id) AS count FROM customers_services_details
                            WHERE service_id = s.id
                            GROUP BY s.id
                    ),
                    s.price_buy
                )) * 100) AS per_utile'),
			])
		    ->where('csd_c.service_id', $id)
			->groupBy('c.id')
			->orderBy('company', 'ASC')
			->get();

//	    dd($customers);

	    $services_details = DB::table('customers_services')
		    ->join(
			    'customers_services_details',
			    'customers_services_details.customer_service_id', '=', 'customers_services.id'
		    )
		    ->join(
			    'services AS s',
			    's.id', '=', 'customers_services_details.service_id'
		    )
		    ->select([
			    'customers_services.name as name',
			    'customers_services.reference as reference_service',
			    'customers_services.expiration as expiration',

			    'customers_services_details.id as id',
			    'customers_services_details.customer_id as customer_id',
		    	'customers_services_details.service_id as service_id',
		    	'customers_services_details.reference as reference_detail',
			    'customers_services_details.price_sell as price_sell',

//			    's.price_buy as price_buy',

			    DB::raw('IF(
					s.is_share = 1,
					s.price_buy / (
						SELECT COUNT(id) AS count FROM customers_services_details
							WHERE service_id = s.id
							GROUP BY s.id
					),
					s.price_buy) AS price_buy'),

			    DB::raw('(customers_services_details.price_sell - IF(
					s.is_share = 1,
					s.price_buy / (
						SELECT COUNT(id) AS count FROM customers_services_details
							WHERE service_id = s.id
							GROUP BY s.id
					),
					s.price_buy)) AS price_utile'),
			    DB::raw('((customers_services_details.price_sell - IF(
                    s.is_share = 1,
                    s.price_buy / (
                        SELECT COUNT(id) AS count FROM customers_services_details
                            WHERE service_id = s.id
                            GROUP BY s.id
                    ),
                    s.price_buy
                )) / IF(
                    s.is_share = 1,
                    s.price_buy / (
                        SELECT COUNT(id) AS count FROM customers_services_details
                            WHERE service_id = s.id
                            GROUP BY s.id
                    ),
                    s.price_buy
                ) * 100) AS per_utile'),
		    ])
		    ->where('customers_services_details.service_id', $id)
		    ->orderBy('reference_detail')
		    ->get();
//	        ->toSql();

//	    dd($services_details);

        return view('service.form', [
            'service' => $service[0],
	        'customers' => $customers,
	        'services_details' => $services_details
        ]);
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
        $service = \App\Model\Service::find($id);
        $service->name = $request->input('name');
        $service->price_buy = floatval(str_replace(',', '.', $request->input('price_buy')));
        $service->price_sell = floatval(str_replace(',', '.', $request->input('price_sell')));
        $service->is_share = $request->input('is_share');

        $service->save();

        return redirect()->route('service.list', ['s' => $request->session()->get('ss')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        \App\Model\Service::destroy($id);

        return redirect()->route('service.list', ['s' => $request->session()->get('ss')]);
    }
}
