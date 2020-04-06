<?php

namespace App\Http\Controllers;

use App\Model\CustomersServicesDetails;
use Illuminate\Http\Request;

class getData extends Controller
{
    public function totals()
    {
        $services = \App\Model\Service::orderBy('name', 'ASC')
            ->with('details')
            ->get();

        $customersServicesDetails = CustomersServicesDetails::with('service')
            ->get();

        $total_price_buy = 0;
        $total_price_sell = 0;

        foreach ($customersServicesDetails as $serviceDetail) {

            if (isset($serviceDetail->service)) {

                if ($serviceDetail->service->is_share == null) {

                    /**
                     * Sommo i prezzi di acquisto
                     */
                    $total_price_buy += $serviceDetail->service->price_buy;

                } else {

                    /**
                     * Divido il prezzo per i servizi condivisi
                     * e lo sommo ai prezzi di acquisto
                     */
                    foreach ($services as $service) {

                        if ($service->id == $serviceDetail->service_id) {

                            $total_price_buy += $serviceDetail->service->price_buy / count($service->details);

                        }

                    }

                }

                /**
                 * Sommo i prezzi di vendita
                 */
                $total_price_sell += $serviceDetail->price_sell;

            }
        }

        $totals_array = array(
            'price_buy' => $total_price_buy,
            'price_sell' => $total_price_sell,
            'price_utile' => $total_price_sell - $total_price_buy,
        );

        return $totals_array;
    }
}
