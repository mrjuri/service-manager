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
    protected $db_prefix;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        $this->db_prefix = env('DB_PREFIX');
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
                DB::raw('SUM(' . $this->db_prefix . 'csd_c.price_sell) AS price_sell'),
                DB::raw('SUM(IF(
                        ' . $this->db_prefix . 's.is_share = 1,
                        ' . $this->db_prefix . 's.price_buy / (
                            SELECT COUNT(id) AS count FROM ' . $this->db_prefix . 'customers_services_details
                                WHERE service_id = ' . $this->db_prefix . 's.id
                                GROUP BY ' . $this->db_prefix . 's.id
                        ),
                        ' . $this->db_prefix . 's.price_buy
                    )) AS price_buy'),
                DB::raw('(SUM(' . $this->db_prefix . 'csd_c.price_sell) - SUM(IF(
                        ' . $this->db_prefix . 's.is_share = 1,
                        ' . $this->db_prefix . 's.price_buy / (
                            SELECT COUNT(id) AS count FROM ' . $this->db_prefix . 'customers_services_details
                                WHERE service_id = ' . $this->db_prefix . 's.id
                                GROUP BY ' . $this->db_prefix . 's.id
                        ),
                        ' . $this->db_prefix . 's.price_buy
                    ))) AS price_utile'),
                DB::raw('(SUM(' . $this->db_prefix . 'csd_c.price_sell) - SUM(IF(
                        ' . $this->db_prefix . 's.is_share = 1,
                        ' . $this->db_prefix . 's.price_buy / (
                            SELECT COUNT(id) AS count FROM ' . $this->db_prefix . 'customers_services_details
                                WHERE service_id = ' . $this->db_prefix . 's.id
                                GROUP BY ' . $this->db_prefix . 's.id
                        ),
                        ' . $this->db_prefix . 's.price_buy
                    ))) / (' . $totals['price_utile'] . ') * 100 AS per'),
                DB::raw('((SUM(' . $this->db_prefix . 'csd_c.price_sell) - SUM(IF(
                        ' . $this->db_prefix . 's.is_share = 1,
                        ' . $this->db_prefix . 's.price_buy / (
                            SELECT COUNT(id) AS count FROM ' . $this->db_prefix . 'customers_services_details
                                WHERE service_id = ' . $this->db_prefix . 's.id
                                GROUP BY ' . $this->db_prefix . 's.id
                        ),
                        ' . $this->db_prefix . 's.price_buy
                    ))) / SUM(IF(
                        ' . $this->db_prefix . 's.is_share = 1,
                        ' . $this->db_prefix . 's.price_buy / (
                            SELECT COUNT(id) AS count FROM ' . $this->db_prefix . 'customers_services_details
                                WHERE service_id = ' . $this->db_prefix . 's.id
                                GROUP BY ' . $this->db_prefix . 's.id
                        ),
                        ' . $this->db_prefix . 's.price_buy
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
        $customer->piva = $request->input('piva');
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
        $customer->piva = $request->input('piva');
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

        /**
         * Variabili del servizio
         */
        $service_id = $request->input('service_id');
        $service_expiration = $request->input('service_expiration');
        $service_reference = $request->input('service_reference');
        $service_piva = $request->input('service_piva');
        $service_company = $request->input('service_company');
        $service_email = $request->input('service_email');
        $service_customer_name = $request->input('service_customer_name');

        /**
         * Variabili dei dettagli del servizio
         */
        $service_details_id = $request->input('service_details_id');
        $service_details = $request->input('service_details');
        $service_details_reference = $request->input('service_details_reference');
        $service_details_price_sell = $request->input('service_details_price_sell');

        foreach ($request->input('service_name') as $k => $service_name) {

            /**
             * Salvo il servizio del cliente
             */
            $customerService = new CustomersServices();

            $customerService->customer_id = $customer_id;
            $customerService->name = $service_name;

            if (isset($service_id[$k]))
                $customerService->id = $service_id[$k];

            if (isset($service_expiration[$k])) {
                $customerService->expiration = date('YmdHis',
                    strtotime(str_replace('/', '-', $service_expiration[$k]) . ' 00:00:00')
                );
            }

            if (isset($service_reference[$k]))
                $customerService->reference = $service_reference[$k];

            if (isset($service_piva[$k]))
                $customerService->piva = $service_piva[$k];

            if (isset($service_company[$k]))
                $customerService->company = $service_company[$k];

            if (isset($service_email[$k]))
                $customerService->email = $service_email[$k];

            if (isset($service_customer_name[$k]))
                $customerService->customer_name = $service_customer_name[$k];

            $customerService->save();

            if (isset($service_details[$k])) {

                foreach ($service_details[$k] as $k_detail => $service_detail_id) {

                    if ($service_detail_id) {

                        /**
                         * Salvo il dettaglio del servizio del cliente
                         */
                        $customerServiceDetail = new CustomersServicesDetails();

                        $customerServiceDetail->customer_id = $customer_id;
                        $customerServiceDetail->service_id = intval($service_detail_id);
                        $customerServiceDetail->customer_service_id = $customerService->id;

                        if (isset($service_details_id[$k][$k_detail]))
                            $customerServiceDetail->id = $service_details_id[$k][$k_detail];

                        if (isset($service_details_reference[$k][$k_detail]))
                            $customerServiceDetail->reference = $service_details_reference[$k][$k_detail];

                        if (isset($service_details_price_sell[$k][$k_detail])) {
                            $customerServiceDetail->price_sell = floatval(
                                str_replace(',', '.', $service_details_price_sell[$k][$k_detail])
                            );
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
     */
    public function renew_service($id)
    {
        $customerService = \App\Model\CustomersServices::find($id);

        $expirationTime = strtotime($customerService->expiration . ' +1 year');
        $expirationTimestamp = date('YmdHis', $expirationTime);

        $customerService->expiration = $expirationTimestamp;
        $customerService->save();
    }

    /**
     * Rinnovo del servizio e redirect
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function renew($id)
    {
        $this->renew_service($id);

        return redirect()->route('home');
    }
}
