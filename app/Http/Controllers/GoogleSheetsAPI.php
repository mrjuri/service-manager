<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GoogleSheetsAPI extends Controller
{
    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     */
    public function getClient()
    {
        $credentials_path = Storage::disk('public')->path('google_sheets_api/credentials.json');

        $client = new \Google_Client();
        $client->setApplicationName('serviceM');
        $client->setScopes(\Google_Service_Sheets::SPREADSHEETS);
        $client->setAuthConfig($credentials_path);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        return $client;
    }

    public function update()
    {
        /**
         * Creo un array con la definizione delle celle per mese
         */
        $alpha = range('B', 'M');
        $row_in = 3;
        $row_out = 4;

        for ($m = 0; $m < 12; $m++) {

            $range_array[$m + 1] = array(
                'in' => $alpha[$m] . $row_in,
                'out' => $alpha[$m] . $row_out
            );

        }

        /**
         * Prendo i dati da fattureincloud.it
         */
        $fic = new FattureInCloudAPI();

        $fatture_in = $fic->get(
            'fatture',
            'lista',
            array(
                'anno' => env('GOOGLE_SHEETS_YEAR'),
                'data_inizio' => '01/01/' . env('GOOGLE_SHEETS_YEAR'),
                'data_fine' => '31/12/' . env('GOOGLE_SHEETS_YEAR')
            )
        );

        $fic = new FattureInCloudAPI();

        $fatture_out = $fic->get(
            'acquisti',
            'lista',
            array(
                'anno' => env('GOOGLE_SHEETS_YEAR'),
                'data_inizio' => '01/01/' . env('GOOGLE_SHEETS_YEAR'),
                'data_fine' => '31/12/' . env('GOOGLE_SHEETS_YEAR')
            )
        );

        /**
         * Calcolo i totali per mese delle fatture
         */
        $tot_month_in = array();

        foreach ($fatture_in as $fattura) {

            $month_n = date('n', strtotime(str_replace('/', '-', $fattura['data'])));

            if (!isset($tot_month_in[$range_array[$month_n]['in']]))
                $tot_month_in[$range_array[$month_n]['in']] = 0;

            $tot_month_in[$range_array[$month_n]['in']] += $fattura['importo_netto'];

        }

        $tot_month_in = array_reverse($tot_month_in, true);
        $k_tot_month_in = array_keys($tot_month_in);

        // - - -

        $tot_month_out = array();

        foreach ($fatture_out as $fattura) {

            $month_n = date('n', strtotime(str_replace('/', '-', $fattura['data'])));

            if (!isset($tot_month_out[$range_array[$month_n]['out']]))
                $tot_month_out[$range_array[$month_n]['out']] = 0;

            $tot_month_out[$range_array[$month_n]['out']] -= $fattura['importo_netto'];

        }

        $tot_month_out = array_reverse($tot_month_out, true);
        $k_tot_month_out = array_keys($tot_month_out);

        /**
         * Inserisco i dati in Google Sheets
         */
        $range = env('GOOGLE_SHEETS_YEAR') . '!' . $k_tot_month_in[0] . ':' . $k_tot_month_out[count($k_tot_month_out) - 1];

        $values = [
            array_values($tot_month_in),
            array_values($tot_month_out)
        ];

        $client = $this->getClient();
        $service = new \Google_Service_Sheets($client);
        $spreadsheetId = env('GOOGLE_SHEETS_ID');

        $body = new \Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);

        $params = [
            'valueInputOption' => 'RAW'
        ];

        $result = $service->spreadsheets_values->update(
            $spreadsheetId,
            $range,
            $body,
            $params
        );
    }
}
