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
        $template = $this->template($customer_id, $customer_service_id);

        return view('mail.service-expiration', [
            'content' => $template
        ]);
    }

    public function send()
    {

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

    /**
     * Creazione template da inviare via email e visualizzare online
     *
     * @param $customer_id
     * @param $customer_service_id
     *
     * @return string|string[]
     */
    public function template($customer_id, $customer_service_id)
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
        $template = str_replace(
            '[customers_services-expiration]',
            '<div class="date-exp-container">
                        <div class="date-exp">'. date('d-m-Y', strtotime($customers_service->expiration)) . '</div>
                        <div class="date-exp-msg">(dopo questa data, il servizio verrà disattivato)</div>
                    </div>',
            $template
        );

        $customers_services_details = CustomersServicesDetails::with('service')
                                                              ->where('customer_id', $customer_id)
                                                              ->where('customer_service_id', $customer_service_id)
                                                              ->orderBy('price_sell', 'DESC')
                                                              ->get();

        foreach ($customers_services_details as $customer_service_detail) {

            if (!isset($array_rows[$customer_service_detail->service->name_customer_view]))
                $array_rows[$customer_service_detail->service->name_customer_view] = array();

            $array_rows[$customer_service_detail->service->name_customer_view][] = array(
                'reference' => $customer_service_detail->reference,
                'price_sell' => $customer_service_detail->price_sell,
            );

        }

        $price_sell_tot = 0;
        $customers_services_list_ = '<div class="row">';

        foreach ($array_rows as $k => $a) {

            $customers_services_list_ .= '
                <div class="col-lg-6">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-white border-warning">' . $k . '</div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless table-hover">
            ';

            foreach ($a as $v) {

                $customers_services_list_ .= '
                    <tr>
                        <td>' . $v['reference'] . '</td>
                        <td class="text-right">&euro; ' . number_format($v['price_sell'], 2, ',', '.') . '</td>
                    </tr>
                ';

                $price_sell_tot += $v['price_sell'];

            }

            $customers_services_list_ .= '
                        </table>
                    </div>
                </div><br>
                </div>
            ';

        }

        $customers_services_list_ .= '</div>';

        $price_sell_tot = '&euro; ' . number_format($price_sell_tot, 2, ',', '.') . ' + IVA';

        $template = str_replace('[customers_services-list_]', $customers_services_list_, $template);

        $template = str_replace('[customers_services-total_]', $price_sell_tot, $template);

        return $template;
    }
}
