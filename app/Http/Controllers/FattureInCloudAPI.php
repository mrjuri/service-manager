<?php

namespace App\Http\Controllers;

use App\Model\CustomersServices;
use App\Model\CustomersServicesDetails;
use Illuminate\Http\Request;

class FattureInCloudAPI extends Controller
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
     * Creazione fattura e invio tramite email.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $customer_service_id = $request->input('customer_service_id');
        $pagamento_saldato = $request->input('pagamento_saldato');

        /*$info_account = $this->api(
            'info/account',
            array(
                'api_uid' => env('FIC_API_UID'),
                'api_key' => env('FIC_API_KEY'),
                'campi' => [
                    "lista_metodi_pagamento"
                ]
            )
        );

        dd($info_account);*/

        /**
         * Fatture in Cloud
         * Recupero dati dei proditti
         */
        $prodotti_lista = $this->api('prodotti/lista');

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

                /**
                 * Ricerca corrispondenza con l'ID del prodotto di Fatture in Cloud
                 */
                $fic_prodotto_id = 0;

                foreach ($prodotti_lista['lista_prodotti'] as $fic_prodotto) {

                    if ($fic_prodotto['cod'] == $customer_service_detail->service->fic_cod) {

                        $fic_prodotto_id = $fic_prodotto['id'];
                        $fic_prodotto_cod = $fic_prodotto['cod'];
                        $fic_prodotto_categoria = $fic_prodotto['categoria'];
                        break;

                    }

                }

                /**
                 * Array con i valori da inserire nella riga prodotto
                 */
                $array_rows[$customer_service_detail->service->name_customer_view] = array(
                    'fic_id' => $fic_prodotto_id,
                    'fic_cod' => $fic_prodotto_cod,
                    'fic_categoria' => $fic_prodotto_categoria,
                    'price_sell' => $customer_service_detail->price_sell,
                    'reference' => array()
                );

            }

            $array_rows[$customer_service_detail->service->name_customer_view]['reference'][] = $customer_service_detail->reference;
        }

        /**
         * Fatture in Cloud
         * Creazione prodotti per la nuova fattura
         */
        $lista_articoli = array();
        foreach ($array_rows as $k => $a) {

            $a_unique = array_unique($a['reference']);
            sort($a_unique);
            $desc = implode("\n", $a_unique);

            $lista_articoli[] = array(
                'id' => $a['fic_id'],
                'codice' => $a['fic_cod'],
                'categoria' => $a['fic_categoria'],
                'nome' => $k,
                'descrizione' => $desc,
                'quantita' => count($a['reference']),
                'prezzo_netto' => $a['price_sell'],
                'cod_iva' => 0,
            );

        }

        /**
         * Fatture in Cloud
         * Creazione nuova fattura
         */
        $fattura_nuova = $this->api(
            'fatture/nuovo',
            array(
                'nome' => $customer_service->customer_name ? $customer_service->customer_name : $customer_service->customer->name,
                'piva' => $customer_service->piva ? $customer_service->piva : $customer_service->customer->piva,
                'autocompila_anagrafica' => true,
                'mostra_info_pagamento' => true,
                'metodo_id' => env('FIC_metodo_id'),
                'prezzi_ivati' => false,
                'PA' => true,
                'PA_tipo_cliente' => 'B2B',
                'lista_articoli' => $lista_articoli,
                'lista_pagamenti' => array(
                    array(
                        'data_scadenza' => date('d/m/Y'),
                        'importo' => 'auto',
                        'metodo' => $pagamento_saldato == 1 ? env('FIC_metodo_nome') : 'not',
                        'data_saldo' => date('d/m/Y'),
                    )
                )
            )
        );

        $fattura_inviamail = 0;

        if ($fattura_nuova['success'] == 1) {

            /**
             * Fatture in Cloud
             * Recupero dati documento appena creato
             */
            $infomail = $this->api(
                'fatture/infomail',
                array('id' => $fattura_nuova['new_id'])
            );

            /**
             * Fatture in Cloud
             * Invio il documento via email al cliente
             */
            $fattura_inviamail = $this->api(
                'fatture/inviamail',
                array(
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
     * Recupero i dati e li inserisco in un Array.
     *
     * @param $resource
     * @param $action
     * @param array $filter
     *
     * @return mixed
     */
    public function get($resource, $action, $filter = array())
    {
        $fic_result = $this->api(
            $resource . '/' . $action,
            $filter
        );

        if ($resource == 'preventivi' ||
            $resource == 'fatture' ||
            $resource == 'acquisti') {
            $resource = 'documenti';
        }

        return $fic_result[$action . '_' . $resource];
    }

    /**
     * Mi collegato a Fatture in Cloud tramite API
     * e recupero le risorse richieste.
     *
     * @param $resource
     * @param $filter
     *
     * @return mixed
     */
    public function api($resource, $filter = array())
    {
        $url = 'https://api.fattureincloud.it/v1/' . $resource;

        $array_auth = array(
            'api_uid' => env('FIC_API_UID'),
            'api_key' => env('FIC_API_KEY')
        );

        $request = array_merge($filter, $array_auth);

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
