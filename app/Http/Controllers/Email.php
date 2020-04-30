<?php

namespace App\Http\Controllers;

use App\Mail\Expiration;
use App\Model\CustomersServices;
use App\Model\CustomersServicesDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class Email extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($customer_id, $customer_service_id)
    {
        $html = Storage::disk('public')->get('mail_template/expiration.html');
        $content = $this->template($html, $customer_id, $customer_service_id);

        return view('mail.expiration', [
            'content' => $content
        ]);

        /*$settings = \App\Model\Setting::where('name', 'email_body')->get();
        $template = $this->template($settings[0]->value, $customer_id, $customer_service_id);

        return view('mail.service-expiration', [
            'content' => $template
        ]);*/
    }

    public function sendExpiration()
    {
        $customer_id = 30;
        $customer_service_id = 421;

        $html = Storage::disk('public')->get('mail_template/expiration.html');
        $content = $this->template($html, $customer_id, $customer_service_id);

        Mail::to('juri.paiusco@gmail.com')
            ->send(new Expiration($content));

        /*$template = $this->template($customer_id, $customer_service_id);

        Mail::to('juri.paiusco@gmail.com')
            ->send(new Expiration($template));*/
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
    public function template($html, $customer_id, $customer_service_id)
    {
        $customers = \App\Model\Customer::where('id', $customer_id)
                                        ->get();
        $customer = $customers[0];

        $customers_services = CustomersServices::where('id', $customer_service_id)
                                               ->get();
        $customers_service = $customers_services[0];

        if ($customers_service->customer_name) {

            $html = str_replace('[customers-name]', $customers_service->customer_name, $html);

        } else {

            $html = str_replace('[customers-name]', $customer->name, $html);
        }

        $html = str_replace('[customers_services-name]', $customers_service->name, $html);
        $html = str_replace('[customers_services-reference]', $customers_service->reference, $html);
        $html = str_replace(
            '[customers_services-expiration]',
            date('d/m/Y', strtotime($customers_service->expiration)),
            $html
        );

        $style_date_banner = '
            <style>
            h2 {
                margin-bottom: 15px;
            }
            .date-exp-container {
                border: 4px dashed #f00;
                padding: 30px 0 15px 0;
                text-align: center;
                border-radius: 8px;
                margin: 30px 0 0 0;
            }
            .date-exp {
                font-size: 3em;
                font-weight: bold;
                white-space: nowrap;
                margin-bottom: 10px;
            }
            .date-exp-msg {
                font-size: .75em;
                white-space: nowrap;
            }
            </style>
        ';

        $html = str_replace(
            '[customers_services-expiration-banner]',
            $style_date_banner . '<div class="date-exp-container">
                        <div class="date-exp">'. date('d-m-Y', strtotime($customers_service->expiration)) . '</div>
                        <div class="date-exp-msg">(data di scadenza e disattivazione dei servizi)</div>
                    </div>',
            $html
        );

        $customers_services_details = CustomersServicesDetails::with('service')
                                                              ->where('customer_id', $customer_id)
                                                              ->where('customer_service_id', $customer_service_id)
                                                              ->orderBy('price_sell', 'DESC')
                                                              ->orderBy('reference', 'ASC')
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
        $customers_services_list_ = '
        <style>
            .tbl-container {
                background-color: #f5f5f5;
                padding: 5px 15px 5px 15px;
                border-radius: 6px;
            }
            .tbl-details {
                color: #aaa;
                margin-top: 5px;
                margin-bottom: 15px;
            }
            .tbl-details th {
                border-bottom: 1px solid #ccc;
                font-weight: normal;
            }
            .title-service-details {
                margin-bottom: 15px;
                text-align: center;
                color: #aaa;
            }
        </style>
        ';

        $customers_services_list_ .= '
        <div class="title-service-details">
            Gli elementi che compongono il servizio ' . $customers_service->name . ' per ' . $customers_service->reference . '
        </div>';
        $customers_services_list_ .= '<div class="tbl-container">';

        foreach ($array_rows as $k => $a) {

            $customers_services_list_ .= '
            <table width="100%" class="tbl-details">
                <tr>
                    <th colspan="2">' . $k . '</th>
                </tr>
            ';

            foreach ($a as $v) {

                $customers_services_list_ .= '
                    <tr>
                        <td>' . $v['reference'] . '</td>
                        <td align="right">&euro; ' . number_format($v['price_sell'], 2, ',', '.') . '</td>
                    </tr>
                ';

                $price_sell_tot += $v['price_sell'];

            }

            $customers_services_list_ .= '
            </table>
            ';

        }

        $customers_services_list_ .= '</div>';

        $price_sell_tot = $price_sell_tot * 1.22;
        $price_sell_tot = '&euro; ' . number_format($price_sell_tot, 2, ',', '.');

        $html = str_replace('[customers_services-list_]', $customers_services_list_, $html);

        $html = str_replace('[customers_services-total_]', $price_sell_tot, $html);

        $html = str_replace(
            '*|MC:SUBJECT|*',
            '[' . $customers_service->reference . '] - ' . $customers_service->name . ' in scadenza',
            $html);

        $html = str_replace(
            '*|MC_PREVIEW_TEXT|*',
            date('d/m/Y', strtotime($customers_service->expiration)) . ' disattivazione ' . $customers_service->name . ' ' . $customers_service->reference,
            $html);

        return $html;
    }
}
