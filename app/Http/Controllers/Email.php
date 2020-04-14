<?php

namespace App\Http\Controllers;

use App\Model\CustomersServices;
use App\Model\CustomersServicesDetails;
use Illuminate\Http\Request;

class Email extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($customer_id, $customer_service_id)
    {
        $settings = \App\Model\Setting::where('name', 'email_body')
            ->get();
        $template = $settings[0]->value;

        $customers = \App\Model\Customer::where('id', $customer_id)
            ->get();
        $customer = $customers[0];
        $template = str_replace('[customers-name]', $customer->name, $template);

        $customers_services = CustomersServices::where('id', $customer_service_id)
            ->get();
        $customers_service = $customers_services[0];
        $template = str_replace('[customers_services-name]', $customers_service->name, $template);
        $template = str_replace('[customers_services-reference]', $customers_service->reference, $template);
        $template = str_replace('[customers_services-expiration]', date('d/m/Y', strtotime($customers_service->expiration)), $template);

        $customers_services_details = CustomersServicesDetails::where('customer_id', $customer_id)
            ->where('customer_service_id', $customer_service_id)
            ->get();

        $price_sell_tot = 0;
        $customers_services_list_ = '<table class="table table-striped table-bordered table-sm">';

        $customers_services_list_ .= '<tr>';

        $customers_services_list_ .= '<th>rif.</th>';
        $customers_services_list_ .= '<th class="text-right">Importo</th>';

        $customers_services_list_ .= '</tr>';

        foreach ($customers_services_details as $customer_service_detail) {

            $customers_services_list_ .= '<tr>';

            $customers_services_list_ .= '<td>';
            $customers_services_list_ .= $customer_service_detail->reference;
            $customers_services_list_ .= '</td>';
            $customers_services_list_ .= '<td class="text-right">';
            $customers_services_list_ .= '&euro; ' . number_format($customer_service_detail->price_sell, 2, ',', '.');
            $customers_services_list_ .= '</td>';

            $customers_services_list_ .= '</tr>';

            $price_sell_tot += $customer_service_detail->price_sell;

        }

        $customers_services_list_ .= '</table>';

        $price_sell_tot = '&euro; ' . number_format($price_sell_tot, 2, ',', '.') . ' + IVA';

        $template = str_replace('[customers_services-list_]', $customers_services_list_, $template);
        $template = str_replace('[customers_services-total_]', $price_sell_tot, $template);

        return view('mail.service-expiration', [
            'content' => $template
        ]);
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
