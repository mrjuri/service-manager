<?php

namespace App\Http\Controllers;

use App\Model\CustomersServices;
use App\Model\CustomersServicesDetails;
use Illuminate\Http\Request;

class FattureInCloudAPI extends Controller
{
    /**
     * Creazione fattura e invio tramite email.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($customer_service_id)
    {
        /**
         * Recupero dati per la fattura
         */
        $customer_service = CustomersServices::with('customer')
                                             ->with('details')
                                             ->find($customer_service_id);

        $customers_services_details = CustomersServicesDetails::with('service')
                                                              ->where('customer_service_id', $customer_service_id)
                                                              ->orderBy('price_sell', 'DESC')
                                                              ->orderBy('reference', 'ASC')
                                                              ->get();

        foreach ($customers_services_details as $customer_service_detail) {

            if (!isset($array_rows[$customer_service_detail->service->name_customer_view])) {

                $array_rows[$customer_service_detail->service->name_customer_view] = array(
                    'price_sell' => $customer_service_detail->price_sell,
                    'reference' => array()
                );

            }

            $array_rows[$customer_service_detail->service->name_customer_view]['reference'][] = $customer_service_detail->reference;
        }

        /**
         * Creazione prodotti per la nuova fattura
         */
        $lista_articoli = array();
        foreach ($array_rows as $k => $a) {

            $a_unique = array_unique($a['reference']);
            sort($a_unique);
            $desc = implode("\n", $a_unique);

            $lista_articoli[] = array(
                'id' => '0',
                'codice' => '',
                'nome' => $k,
                'descrizione' => $desc,
                'quantita' => count($a['reference']),
                'prezzo_netto' => $a['price_sell'],
                'cod_iva' => 0,
            );

        }

        /**
         * Creazione nuova fattura
         */
        $request = array(
            'api_uid' => env('FIC_API_UID'),
            'api_key' => env('FIC_API_KEY'),
            'nome' => $customer_service->customer_name ? $customer_service->customer_name : $customer_service->customer->name,
            'piva' => $customer_service->piva ? $customer_service->piva : $customer_service->customer->piva,
            'autocompila_anagrafica' => true,
            'mostra_info_pagamento' => true,
            'metodo_pagamento' => env('FIC_metodo_pagamento'),
            'metodo_titoloN' => env('FIC_metodo_titoloN'),
            'metodo_descN' => env('FIC_metodo_descN'),
            'prezzi_ivati' => false,
            'PA' => true,
            'PA_tipo_cliente' => 'B2B',
            'lista_articoli' => $lista_articoli,
            'lista_pagamenti' => array(
                array(
                    'data_scadenza' => date('d/m/Y'),
                    'importo' => 'auto',
                    'metodo' => 'not',
                )
            )
        );

        $fattura_nuova = $this->api(
            'https://api.fattureincloud.it/v1/fatture/nuovo',
            $request
        );

        $fattura_inviamail = 0;

        if ($fattura_nuova['success'] == 1) {

            $infomail = $this->api(
                'https://api.fattureincloud.it/v1/fatture/infomail',
                array(
                    'api_uid' => env('FIC_API_UID'),
                    'api_key' => env('FIC_API_KEY'),
                    'id' => $fattura_nuova['new_id']
                )
            );

            $fattura_inviamail = $this->api(
                'https://api.fattureincloud.it/v1/fatture/inviamail',
                array(
                    'api_uid' => env('FIC_API_UID'),
                    'api_key' => env('FIC_API_KEY'),
                    'id' => $fattura_nuova['new_id'],
                    'mail_mittente' => $infomail['mail_mittente'][0]['mail'],
                    'mail_destinatario' => $customer_service->email ? $customer_service->email : $customer_service->customer->email,
                    'oggetto' => $infomail['oggetto_default'],
                    'messaggio' => $infomail['messaggio_default']
                )
            );
        }

        if ($fattura_inviamail['success'] == true) {

            $customer = new Customer();
            $customer->renew_service($customer_service_id);

            return redirect()->route('home');
        }
    }

    /**
     * @param $url
     * @param $request
     *
     * @return mixed
     */
    public function api($url, $request)
    {
        $options = array(
            "http" => array(
                "header"  => "Content-type: text/json\r\n",
                "method"  => "POST",
                "content" => json_encode($request)
            ),
        );
        $context  = stream_context_create($options);
        $result = json_decode(file_get_contents($url, false, $context), true);

        return $result;
    }
}
