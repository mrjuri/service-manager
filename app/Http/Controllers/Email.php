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
    public function index($id)
    {
        $html = Storage::disk('public')->get('mail_template/expiration.html');
        $content = $this->get_template($html, $id);

//        $this->sendExpiration($id);

        return view('mail.expiration', [
            'content' => $content
        ]);

        /*$settings = \App\Model\Setting::where('name', 'email_body')->get();
        $template = $this->template($settings[0]->value, $customer_id, $customer_service_id);

        return view('mail.service-expiration', [
            'content' => $template
        ]);*/
    }

    public function sendExpiration($id)
    {
        $payment = new Payment();
        $payment->sid_create($id);

        $html = Storage::disk('public')->get('mail_template/expiration.html');
        $content = $this->get_template($html, $id);
        $data_array = $this->get_data($id);

        Mail::to($data_array['to'])
            ->send(new Expiration(
                $data_array['subject'],
                $content
            ));
    }

    public function sendExpirationService($id)
    {
        $this->sendExpiration($id);

        return redirect()->route('home');
    }

    public function sendExpirationList()
    {

    }

    public function get_data($customer_service_id)
    {
        $customer_service = CustomersServices::with('customer')
                                             ->with('details')
                                             ->find($customer_service_id);

        $array = array(
            'to' => $customer_service->email ? $customer_service->email : $customer_service->customer->email,
            'subject' => '[' . $customer_service->reference . '] - ' . $customer_service->name . ' in scadenza',
        );

        return $array;
    }

    /**
     * Restituisco i dati necessari a popolare il template
     *
     * @param $customer_service_id
     *
     * @return array
     */
    public function get_data_template_replace($customer_service_id)
    {
        $customer_service = CustomersServices::with('customer')
                                             ->with('details')
                                             ->find($customer_service_id);

        $payment = \App\Model\Payment::where('customer_service_id', $customer_service_id)
                                     ->where('customer_service_expiration', $customer_service->expiration)
                                     ->first();

        $price_sell_tot = 0;
        foreach ($customer_service->details as $detail) {
            $price_sell_tot += $detail->price_sell;
        }
        $price_sell_tot = '&euro; ' . number_format($price_sell_tot * 1.22, 2, ',', '.');

        $str_replace_array = array(
            '[customers-name]' => $customer_service->customer_name ? $customer_service->customer_name : $customer_service->customer->name,
            '[customers_services-name]' => $customer_service->name,
            '[customers_services-reference]' => $customer_service->reference,
            '[customers_services-expiration]' => date('d/m/Y', strtotime($customer_service->expiration)),
            '[customers_services-expiration-banner_]' => '
                <div class="date-exp-container">
                    <div class="date-exp">'. date('d-m-Y', strtotime($customer_service->expiration)) . '</div>
                    <div class="date-exp-msg">(data di scadenza e disattivazione dei servizi)</div>
                </div>
            ',
            '[customers_services-total_]' => $price_sell_tot,

            'http://[customers_services-link_]' => '[customers_services-link_]',
            'https://[customers_services-link_]' => '[customers_services-link_]',
//            '[customers_services-link_]' => route('payment.checkout', $customer_service->id),
            '[customers_services-link_]' => route('payment.checkout', $payment->sid),

            '*|MC:SUBJECT|*' => '[' . $customer_service->reference . '] - ' . $customer_service->name . ' in scadenza',
            '*|MC_PREVIEW_TEXT|*' => date('d/m/Y', strtotime($customer_service->expiration)) . ' disattivazione ' . $customer_service->name . ' ' . $customer_service->reference,
        );

        return $str_replace_array;
    }

    /**
     * Creazione template da inviare via email e visualizzare online
     *
     * @param $html
     * @param $customer_service_id
     *
     * @return string|string[]
     */
    public function get_template($html, $customer_service_id)
    {
        $str_replace_array = $this->get_data_template_replace($customer_service_id);

        $style_custom = '
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
        $style_custom = str_replace('<style>', '', $style_custom);
        $html = str_replace('</style>', $style_custom, $html);

        foreach ($str_replace_array as $k => $v) {

            $html = str_replace($k, $v, $html);

        }

        return $html;
    }
}
