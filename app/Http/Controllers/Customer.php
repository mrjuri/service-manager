<?php

namespace App\Http\Controllers;

use App\Model\CustomerService;
use App\Model\CustomersServices;
use App\Model\CustomersServicesDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Input;

class Customer extends Controller
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
//        $srv_not = Input::get('srv_not');
        $srv_not = $request->input('srv_not');

        if ($s == '') {
            $request->session()->forget('sc');
        } else {
            $request->session()->put('sc', $s);
        }

        $s_array = explode(' ', $s);
        $srv_not_array = explode(' ', $srv_not);

        $getData = new getData();
        $totals = $getData->totals();

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
                        s.price_buy
                    )) AS price_buy'),
                DB::raw('(SUM(csd_c.price_sell) - SUM(IF(
                        s.is_share = 1,
                        s.price_buy / (
                            SELECT COUNT(id) AS count FROM customers_services_details
                                WHERE service_id = s.id
                                GROUP BY s.id
                        ),
                        s.price_buy
                    ))) AS price_utile'),
                DB::raw('(SUM(csd_c.price_sell) - SUM(IF(
                        s.is_share = 1,
                        s.price_buy / (
                            SELECT COUNT(id) AS count FROM customers_services_details
                                WHERE service_id = s.id
                                GROUP BY s.id
                        ),
                        s.price_buy
                    ))) / (' . $totals['price_utile'] . ') * 100 AS per'),
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
            ->where(function ($q) use ($s_array){

                foreach ($s_array as $s) {
                    $q->orWhere('c.name', 'LIKE', '%' . $s . '%')
                        ->orWhere('c.company', 'LIKE', '%' . $s . '%');
                }

            })
            ->groupBy('c.id')
            ->orderBy('price_utile', 'DESC')
            ->get();

//        dd($customers);

        return view('customer.list', [
            'customers' => $customers,
            'totals' => $totals,
            's' => $s
        ]);
    }

    public function search(Request $request)
    {
        $customers = \App\Model\Customer::orderBy('company')
            ->with('details')
            ->get();

        return view('customer.list', [
            'customers' => $customers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $services = \App\Model\Service::orderBy('name')->get();

        $customersServices = array(
            'name' => '',
            'details' => array(
                'service_id' => ''
            )
        );

        $customersServicesObj[0] = (object) $customersServices;

        return view('customer.form', [
            'services' => $services,
            'customersServices' => $customersServicesObj
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
        $customer = new \App\Model\Customer();
        $customer->company = $request->input('company');
        $customer->name = $request->input('name');
        $customer->email = $request->input('email');

        $customer->save();

        $this->set_services($request, $customer->id);

        return redirect()->route('customer.list', ['s' => $request->session()->get('sc')]);
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
        $customer = \App\Model\Customer::where('id', $id)->get();
        $services = \App\Model\Service::orderBy('name')->get();
        $customersServices = CustomersServices::where('customer_id', $id)->with('details')->get();

        return view('customer.form', [
            'customer' => $customer[0],
            'services' => $services,
            'customersServices' => $customersServices
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
        $customer = \App\Model\Customer::find($id);
        $customer->company = $request->input('company');
        $customer->name = $request->input('name');
        $customer->email = $request->input('email');

        $customer->save();

        $this->set_services($request, $id);

        return redirect()->route('customer.list', ['s' => $request->session()->get('sc')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        \App\Model\Customer::destroy($id);
        CustomersServices::where('customer_id', $id)->delete();
        CustomersServicesDetails::where('customer_id', $id)->delete();

        return redirect()->route('customer.list', ['s' => $request->session()->get('sc')]);
    }

    /**
     * Imposta i servizi attivi
     *
     * @param Request $request
     * @param $customer_id
     */
    public function set_services(Request $request, $customer_id)
    {
        CustomersServices::where('customer_id', $customer_id)->delete();
        CustomersServicesDetails::where('customer_id', $customer_id)->delete();

        foreach ($request->input('service_name') as $k => $service_name) {

            $customerService = new CustomersServices();

            $expiration = $request->input('service_expiration');
            $reference = $request->input('service_reference');
            $company = $request->input('service_company');
            $email = $request->input('service_email');
            $customer_name = $request->input('service_customer_name');

            $customerService->customer_id = $customer_id;
            $customerService->name = $service_name;

            if (isset($reference[$k])) {
                $customerService->reference = $reference[$k];
            }

            if (isset($company[$k])) {
                $customerService->company = $company[$k];
            }

            if (isset($company[$k])) {
                $customerService->email = $email[$k];
            }

            if (isset($company[$k])) {
                $customerService->customer_name = $customer_name[$k];
            }

            if (isset($expiration[$k])) {
                $customerService->expiration = date('YmdHis',
                    strtotime(str_replace('/', '-', $expiration[$k]) . ' 00:00:00')
                );
            }

            $customerService->save();

            $service_details = $request->input('service_details');

            if (is_array($service_details[$k])) {

                foreach ($service_details[$k] as $k_detail => $service_id) {

                	if ($service_id != '') {

		                $customerServiceDetail = new CustomersServicesDetails();

		                $reference = $request->input('service_details_reference');
		                $price_sell = $request->input('service_details_price_sell');

		                $customerServiceDetail->customer_id = $customer_id;
		                $customerServiceDetail->service_id = intval($service_id);
		                $customerServiceDetail->customer_service_id = $customerService->id;

		                if (isset($reference[$k][$k_detail])) {
			                $customerServiceDetail->reference = $reference[$k][$k_detail];
		                }

		                if (isset($price_sell[$k][$k_detail])) {
			                $customerServiceDetail->price_sell = floatval(str_replace(',', '.', $price_sell[$k][$k_detail]));
		                }

		                $customerServiceDetail->save();

	                }

                }

            }

        }
    }

    /**
     * Rinnovo del servizio
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function renew($id)
    {
        $customerService = \App\Model\CustomersServices::find($id);

        $expirationTime = strtotime($customerService->expiration . ' +1 year');
        $expirationTimestamp = date('YmdHis', $expirationTime);

        $customerService->expiration = $expirationTimestamp;
        $customerService->save();

        return redirect()->route('home');
    }
}
