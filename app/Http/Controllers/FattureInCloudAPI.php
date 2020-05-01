<?php

namespace App\Http\Controllers;

use App\Model\CustomersServices;
use App\Model\CustomersServicesDetails;
use Illuminate\Http\Request;

class FattureInCloudAPI extends Controller
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
    public function create($customer_id, $customer_service_id)
    {
        $customer = \App\Model\Customer::find($customer_id);
        $customer_service = CustomersServices::find($customer_service_id);
        $customers_services_details = CustomersServicesDetails::with('service')
                                                              ->where('customer_id', $customer_id)
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

        $lista_articoli = array();
        foreach ($array_rows as $k => $a) {

            $a_unique = array_unique($a['reference']);
            sort($a_unique);
            $desc = implode("\n", $a_unique);

            $lista_articoli[] = array(
                "id" => "0",
                "codice" => "",
                "nome" => $k,
                "descrizione" => $desc,
                "quantita" => count($a['reference']),
                "prezzo_netto" => $a['price_sell'],
                "cod_iva" => 0,
            );

        }

        $url = "https://api.fattureincloud.it/v1/fatture/nuovo";
        $request = array(
            "api_uid" => env('FIC_API_UID'),
            "api_key" => env('FIC_API_KEY'),
            "nome" => $customer_service->customer_name ? $customer_service->customer_name : $customer->name,
            "piva" => $customer_service->piva ? $customer_service->piva : $customer->piva,
            "autocompila_anagrafica" => true,
            "mostra_info_pagamento" => true,
            "metodo_pagamento" => env('FIC_metodo_pagamento'),
            "metodo_titoloN" => env('FIC_metodo_titoloN'),
            "metodo_descN" => env('FIC_metodo_descN'),
            "prezzi_ivati" => false,
            "PA" => true,
            "PA_tipo_cliente" => "B2B",
            "lista_articoli" => $lista_articoli,
            "lista_pagamenti" => array(
                array(
                    "data_scadenza" => date('d/m/Y'),
                    "importo" => 'auto',
                    "metodo" => "not",
                )
            )
        );

        $options = array(
            "http" => array(
                "header"  => "Content-type: text/json\r\n",
                "method"  => "POST",
                "content" => json_encode($request)
            ),
        );
        $context  = stream_context_create($options);
        $result = json_decode(file_get_contents($url, false, $context), true);
        print_r($result);
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
