<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoogleSheetsAPI extends Controller
{
    public function fattureImport()
    {
        $fic = new FattureInCloudAPI();

        $fatture_in = $fic->get(
            'fatture',
            'lista',
            array(
                'anno' => 2020,
                'data_inizio' => '01/04/2020',
                'data_fine' => '31/06/2020'
            )
        );

        $fatture_out = $fic->get(
            'acquisti',
            'lista',
            array(
                'anno' => 2020,
                'data_inizio' => '01/04/2020',
                'data_fine' => '31/06/2020'
            )
        );

        $tot_netto_in = 0;

        foreach ($fatture_in as $d) {

            $tot_netto_in += $d['importo_netto'];

        }

        $tot_netto_out = 0;

        foreach ($fatture_out as $d) {

            $tot_netto_out += $d['importo_netto'];

        }

        dd($tot_netto_in - $tot_netto_out);
    }
}
