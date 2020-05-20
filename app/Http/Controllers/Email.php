<?php

namespace App\Http\Controllers;

use App\Mail\Service;
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

    }

    /**
     * Mostra la mail tramite browser.
     *
     * @param $view
     * @param $sid
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function show($view, $sid)
    {
        $payment = \App\Model\Payment::firstWhere('sid', $sid);

        /**
         * Se il link o il servizio non vengono trovati, mostrare una pagina notfound.
         */
        if (!$payment) {
            return view('payment.nofound');
        }

        $html = Storage::disk('public')->get('mail_template/' . $view . '.html');
        $content = $this->get_template($sid, $view, $html);

        return view('mail.service-msg', [
            'content' => $content
        ]);
    }

    /**
     * Invio email di avviso per la scadenza del servizio.
     * Vengono inviate email CC nel caso siano presenti.
     *
     * Il redirect non è presente, perché questo modulo può essere
     * richiamato per inviare le mail alla lista dei clienti con servizio
     * in scadenza.
     *
     * @param $id
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function sendExpiration($id)
    {
        $payment = new Payment();
        $sid = $payment->sid_create($id);

        $html = Storage::disk('public')->get('mail_template/expiration.html');
        $content = $this->get_template($sid, 'expiration', $html);
        $data_array = $this->get_data($id);

        $email_array = explode(';', $data_array['to']);

        $mail = Mail::to($email_array[0]);

        if (count($email_array) > 0) {

            foreach ($email_array as $k => $email) {
                if ($k > 0) {
                    $mail->cc($email);
                }
            }

        }

        if (env('MAIL_BCC_ADDRESS')) {
            $mail->bcc(env('MAIL_BCC_ADDRESS'));
        }

        $mail->send(new Service(
            $data_array['subject_expiration'],
            $content
        ));
    }

    /**
     * Invio email con redirect.
     *
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function sendExpirationService($id)
    {
        $this->sendExpiration($id);

        return redirect()->route('home');
    }

    /**
     * Dopo aver confermato il rinnovo, viene inviata questa email di conferma.
     * @param $sid
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function sendConfirmService($sid)
    {
        $payment = \App\Model\Payment::firstWhere('sid', $sid);

        $html = Storage::disk('public')->get('mail_template/confirm-' . $payment->type . '.html');
        $content = $this->get_template($sid, 'confirm-' . $payment->type, $html);
        $data_array = $this->get_data($payment->customer_service_id);

        $email_array = explode(';', $data_array['to']);

        $mail = Mail::to($email_array[0]);

        if (env('MAIL_BCC_ADDRESS')) {
            $mail->bcc(env('MAIL_BCC_ADDRESS'));
        }

        $mail->send(new Service(
            $data_array['subject_confirm_' . $payment->type],
            $content
        ));
    }

    /**
     * Invio email di avvenuto pagamento.
     *
     * Ancora da collegare, perché l'email della fattura viene inviata con FattureinCloud
     * e non vorrei diventasse una comunicazione rindondante.
     *
     * @param $sid
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function sendConfirmPayment($sid)
    {
        $payment = \App\Model\Payment::firstWhere('sid', $sid);

        $html = Storage::disk('public')->get('mail_template/confirm-' . $payment->type . '.html');
        $content = $this->get_template($sid, 'confirm-' . $payment->type, $html);
        $data_array = $this->get_data($payment->customer_service_id);

        $email_array = explode(';', $data_array['to']);
    }

    /**
     * Invio multiplo degli avvisi per la scadenza dei servizi.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function sendExpirationList()
    {
        $customers_services = CustomersServices::select([
                                                    'customers_services.id AS id',
                                                    /*'customers_services.customer_id AS customer_id',
                                                    'customers_services.piva AS piva',
                                                    'customers_services.company AS company',
                                                    'customers_services.email AS email',
                                                    'customers_services.customer_name AS customer_name',
                                                    'customers_services.name AS name',
                                                    'customers_services.reference AS reference',
                                                    'customers_services.expiration AS expiration',
                                                    'payments.type AS payment_type',*/
                                                ])
                                               ->leftJoin('payments', function($join) {
                                                   $join->on('payments.customer_service_id', '=', 'customers_services.id');
                                                   $join->on('payments.customer_service_expiration', '=', 'customers_services.expiration');
                                               })
                                               ->where('expiration', '>', date('YmdHis'))
                                               ->where('expiration', '<', date('YmdHis', strtotime('+2 month')))
                                               ->where(function ($query){
                                                   $query->where('payments.type', '')
                                                         ->orWhereNull('payments.type');
                                               })
                                               ->orderBy('expiration', 'ASC')
                                               ->get();

        foreach ($customers_services as $customer_service) {

            $this->sendExpiration($customer_service->id);

        }
    }

    /**
     * Prendo i dati per popolare la mail da inviare.
     *
     * @param $customer_service_id
     *
     * @return array
     */
    public function get_data($customer_service_id)
    {
        $customer_service = CustomersServices::with('customer')
                                             ->with('details')
                                             ->find($customer_service_id);

        $array = array(
            'to' => $customer_service->email ? $customer_service->email : $customer_service->customer->email,
            'subject_expiration' => '[' . $customer_service->reference . '] - ' . $customer_service->name . ' in scadenza',
            'subject_confirm_bonifico' => '[' . $customer_service->reference . '] - Richiesta bonifico bancario ' . $customer_service->name,
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
    public function get_data_template_replace($sid, $view)
    {
        $payment = \App\Model\Payment::firstWhere('sid', $sid);

        $customer_service = CustomersServices::with('customer')
                                             ->with('details')
                                             ->find($payment->customer_service_id);

        $price_sell_tot = 0;
        foreach ($customer_service->details as $detail) {
            $price_sell_tot += $detail->price_sell;
        }
        $price_sell_tot = '&euro; ' . number_format($price_sell_tot * 1.22, 2, ',', '.');

        $str_replace_array = array(
            '[customers-name]' => $customer_service->customer_name ? $customer_service->customer_name : $customer_service->customer->name,
            '[customers_services-name]' => $customer_service->name,
            '[customers_services-reference]' => $customer_service->reference,
            '[customers_services-expiration]' => date('d/m/Y', strtotime($payment->customer_service_expiration)),
            '[customers_services-expiration-banner_]' => '
                <div class="date-exp-container">
                    <div class="date-exp">'. date('d-m-Y', strtotime($payment->customer_service_expiration)) . '</div>
                    <div class="date-exp-msg">(data di scadenza e disattivazione dei servizi)</div>
                </div>
            ',
            '[customers_services-total_]' => $price_sell_tot,

            'http://[customers_services-link_]' => '[customers_services-link_]',
            'https://[customers_services-link_]' => '[customers_services-link_]',
            '[customers_services-link_]' => route('payment.checkout', $sid),

            'http://[email-link_]' => '[email-link_]',
            'https://[email-link_]' => '[email-link_]',
            '[email-link_]' => route('email.show', [$view, $sid]),

            '*|MC:SUBJECT|*' => '[' . $customer_service->reference . '] - ' . $customer_service->name . ' in scadenza',
            '*|MC_PREVIEW_TEXT|*' => date('d/m/Y', strtotime($payment->customer_service_expiration)) . ' disattivazione ' . $customer_service->name . ' ' . $customer_service->reference,
        );

        return $str_replace_array;
    }

    /**
     * Creazione template da inviare via email e visualizzare online
     *
     * @param $customer_service_id
     * @param $view
     * @param $html
     *
     * @return string|string[]
     */
    public function get_template($sid, $view, $html)
    {
        $str_replace_array = $this->get_data_template_replace($sid, $view);

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
