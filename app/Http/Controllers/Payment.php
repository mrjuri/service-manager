<?php

namespace App\Http\Controllers;

use App\Model\CustomersServices;
use App\Model\CustomersServicesDetails;
use Illuminate\Http\Request;

class Payment extends Controller
{
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customer_service = CustomersServices::with('customer')
                                             ->with('details')
                                             ->find($id);

        $customers_services_details = CustomersServicesDetails::with('service')
                                                              ->where('customer_service_id', $id)
                                                              ->orderBy('price_sell', 'DESC')
                                                              ->orderBy('reference', 'ASC')
                                                              ->get();

//        dd($customers_services_details);

        foreach ($customers_services_details as $customer_service_detail) {

            if (!isset($array_services_rows[$customer_service_detail->service->name_customer_view])) {

                $array_services_rows[$customer_service_detail->service->name_customer_view] = array(
                    'price_sell' => $customer_service_detail->service->price_sell,
                    'price_customer_sell' => $customer_service_detail->price_sell,
                    'is_share' => $customer_service_detail->service->is_share,
                    'reference' => array()
                );

            }

            $array_services_rows[$customer_service_detail->service->name_customer_view]['reference'][] = $customer_service_detail->reference;
        }

        $fattureincloud = new FattureInCloudAPI();
        $cliente = $fattureincloud->api(
            'clienti/lista',
            array(
                'api_uid' => env('FIC_API_UID'),
                'api_key' => env('FIC_API_KEY'),
                'piva' => $customer_service->piva ? $customer_service->piva : $customer_service->customer->piva
            )
        );

        return view('payment.checkout', [
            'customer_service' => $customer_service,
            'customers_services_details' => $customers_services_details,
            'array_services_rows' => $array_services_rows,
            'cliente' => $cliente['lista_clienti'][0],
        ]);
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
