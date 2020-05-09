<?php

namespace App\Http\Controllers;

use App\Model\CustomersServices;
use App\Model\CustomersServicesDetails;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Input;

class Dashboard extends Controller
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

    public function view(Request $request)
    {
//        $s = Input::get('s');
        $s = $request->input('s');
        $s_array = explode(' ', $s);

        $servicesList = DB::table('services')
            ->join('customers_services_details',
                'customers_services_details.service_id', '=', 'services.id')
            ->join('customers',
                'customers.id', '=', 'customers_services_details.customer_id')
            ->select([
                'services.name AS name',
                DB::raw('COUNT(customers_services_details.service_id) AS customers_n'),
                DB::raw('IF(
                    services.is_share = 1,
                    services.price_buy,
                    services.price_buy * COUNT(customers_services_details.service_id)
                ) AS price_buy'),
                DB::raw('SUM(customers_services_details.price_sell) AS price_sell'),
                DB::raw('IF(
                    services.is_share = 1,
                    SUM(customers_services_details.price_sell) - services.price_buy,
                    SUM(customers_services_details.price_sell) - (
                        services.price_buy * COUNT(customers_services_details.service_id)
                    )
                ) AS price_utile'),
            ])

            ->where(function ($q) use ($s_array){

                foreach ($s_array as $s) {
                    $q->orWhere('customers.name', 'LIKE', '%' . $s . '%')
                        ->orWhere('customers.company', 'LIKE', '%' . $s . '%');
                }

            })

            ->groupBy('services.id')
            ->orderBy('price_utile', 'DESC')
            ->get();

        /*$services = \App\Model\Service::orderBy('name', 'ASC')
            ->with('details')
            ->get();*/

        $customersServices = CustomersServices::/*join(
                'customers_services_details',
                'customers_services_details.customer_service_id', '=', 'customers_services.id'
            )
            ->join(
                'services',
                'services.id', '=', 'customers_services_details.service_id'
            )
            ->*/select([
                'customers_services.id AS id',
                'customers_services.customer_id AS customer_id',
                'customers_services.piva AS piva',
                'customers_services.company AS company',
                'customers_services.email AS email',
                'customers_services.customer_name AS customer_name',
                'customers_services.name AS name',
                'customers_services.reference AS reference',
                'customers_services.expiration AS expiration',
                'payments.type AS payment_type',
            ])
            ->leftJoin('payments', function($join) {
                $join->on('payments.customer_service_id', '=', 'customers_services.id');
                $join->on('payments.customer_service_expiration', '=', 'customers_services.expiration');
            })
            ->with([
                'customer',
                'details',
                'details.service',
//                'payment'
            ])
            ->orWhereHas('customer', function ($q) use ($s_array) {

                $q->where(function ($q) use ($s_array){

                    foreach ($s_array as $s) {

                        $q->orWhere('customers.name', 'LIKE', '%' . $s . '%')
                            ->orWhere('customers.company', 'LIKE', '%' . $s . '%');
                    }

                });

            })
            /*->orWhereHas('details', function ($q) use ($s_array) {

                $q->orWhereHas('service', function ($q) use ($s_array) {

                    foreach ($s_array as $s) {

                        $q->orWhere('name', 'LIKE', '%' . $s . '%');

                    }

                });

            })*/
            ->orWhereHas('details.service', function ($q) use ($s_array) {

                $q->where(function ($q) use ($s_array){

                    foreach ($s_array as $s) {

                        $q->select('services.id')->orWhere('services.name', 'LIKE', '%' . $s . '%');

                    }

                });

            })
            ->orderBy('expiration')
            ->get();

//        dd($customersServices);

        /*$customersServicesDetails = CustomersServicesDetails::with('service')
            ->get();*/

        $months_services = array();
        $months_array = array('gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre');

        foreach ($months_array as $i => $month) {

            $months_services[$i + 1]['month'] = $months_array[$i];

        }

        foreach ($customersServices as $customersService) {

            $i = date('n', strtotime($customersService->expiration));

            $months_services[$i]['month'] = $months_array[$i - 1];

            if (!isset($months_services[$i]['price_sell']))
            {
                $months_services[$i]['price_sell'] = 0;
            }

            if (!isset($months_services[$i]['price_buy']))
            {
                $months_services[$i]['price_buy'] = 0;
            }

            foreach ($customersService->details as $detail) {

                if (isset($detail->service)) {

                    $months_services[$i]['price_sell'] += $detail->price_sell;

                    if ($detail->service->is_share) {

                        $customersServicesDetailsCount = CustomersServicesDetails::where('service_id', $detail->service->id)
                            ->with('service')
                            ->count();

                        $months_services[$i]['price_buy'] += $detail->service->price_buy / $customersServicesDetailsCount;

                    } else {

                        $months_services[$i]['price_buy'] += $detail->service->price_buy;
                    }

                }
            }

            $months_services[$i]['price_utile'] = $months_services[$i]['price_sell'] - $months_services[$i]['price_buy'];

        }

        ksort($months_services);

        /**
         * Seleziono tutti i clienti attivi
         */
        $customers = DB::table('customers')
            ->join(
                'customers_services_details',
                'customers_services_details.customer_id', '=', 'customers.id'
            )
            ->select([
                'customers.id AS id',
                'customers.name AS name',
                'customers.company AS company',
                DB::raw('SUM(customers_services_details.price_sell) AS price_sell')
            ])
            ->where('price_sell', '>', 0)
            ->groupBy('customers.id')
            ->orderBy('price_sell', 'DESC')
            ->get();

        /**
         * Selezioni abbonamenti attivi
         */
        $customersServicesActive = CustomersServices::count();

        $getData = new getData();
        $totals = $getData->totals();

        return view('dashboard.view', [
//            'services' => $services,
//            'customersServicesDetails' => $customersServicesDetails,
            'customers' => $customers,
            'customersServices' => $customersServices,
            'customersServicesActive' => $customersServicesActive,
            'servicesList' => $servicesList,
            'months_services' => $months_services,
            'totals' => $totals,
            /*'total_price_buy' => $totals['price_buy'],
            'total_price_sell' => $totals['price_sell'],
            'total_price_utile' => $totals['price_utile'],*/
            's' => $s,
        ]);
    }
}
