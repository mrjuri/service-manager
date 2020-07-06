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

        $fatture_attive = $fic->get(
            'fatture',
            'lista',
            array(
                'anno' => env('GOOGLE_SHEETS_YEAR'),
                'data_inizio' => '01/01/' . env('GOOGLE_SHEETS_YEAR'),
                'data_fine' => '31/12/' . env('GOOGLE_SHEETS_YEAR')
            )
        );

        $fic = new FattureInCloudAPI();

        $fatture_passive = $fic->get(
            'acquisti',
            'lista',
            array(
                'anno' => env('GOOGLE_SHEETS_YEAR'),
                'data_inizio' => '01/01/' . env('GOOGLE_SHEETS_YEAR'),
                'data_fine' => '31/12/' . env('GOOGLE_SHEETS_YEAR')
            )
        );

        /**
         * Calcolo i totali per mese delle fatture attive e passive
         * in più calcolo le tasse a debito e a credito
         */
        $tot_month_attivo = array();
        $iva_debito = array();

        foreach ($fatture_attive as $fattura) {

            // Calcolo importo netto in attivo per mese
            $month_n = date('n', strtotime(str_replace('/', '-', $fattura['data'])));

            if (!isset($tot_month_attivo[$range_array[$month_n]['in']])) {
                $tot_month_attivo[$range_array[$month_n]['in']] = 0;
            }

            $tot_month_attivo[$range_array[$month_n]['in']] += $fattura['importo_netto'];

            // Calcolo tasse (IVA) da debito per mese
            if (!isset($iva_debito[$month_n])) {
                $iva_debito[$month_n] = 0;
            }

            // Verifica se PA per Split Payment
            if ($fattura['PA_tipo_cliente'] != 'PA') {
                $iva_debito[$month_n] += $fattura['importo_totale'] - $fattura['importo_netto'];
            }

        }

        $tot_month_attivo = array_reverse($tot_month_attivo, true);
        $k_tot_month_attivo = array_keys($tot_month_attivo);

        // - - -

        $tot_month_passivo = array();
        $iva_credito = array();

        foreach ($fatture_passive as $fattura) {

            // Calcolo importo netto in passivo per mese
            $month_n = date('n', strtotime(str_replace('/', '-', $fattura['data'])));

            if (!isset($tot_month_passivo[$range_array[$month_n]['out']])) {
                $tot_month_passivo[$range_array[$month_n]['out']] = 0;
            }

            $tot_month_passivo[$range_array[$month_n]['out']] -= $fattura['importo_netto'];

            // Calcolo tasse (IVA) a credito per mese
            if (!isset($iva_credito[$month_n])) {
                $iva_credito[$month_n] = 0;
            }

            $iva_credito[$month_n] += $fattura['importo_iva'];

        }

        // Sistemo l'array dei totali per importarli correttamente in Google Sheets
        $tot_month_passivo = array_reverse($tot_month_passivo, true);
        $k_tot_month_passivo = array_keys($tot_month_passivo);

        // - - -

        // Calcolo l'iva per mese
        $iva_count = count($iva_credito) > count($iva_debito) ? count($iva_credito) : count($iva_debito);
        $iva_month = array();

        for ($i = 1; $i <= $iva_count; $i++) {

            if (!isset($iva_debito[$i])) {
                $iva_debito[$i] = 0;
            }

            if (!isset($iva_credito[$i])) {
                $iva_credito[$i] = 0;
            }

            $iva_month[$i] = $iva_credito[$i] - $iva_debito[$i];

        }

        // Calcolo l'iva per singolo trimestre
        $iva_trimestre = array();

        foreach (array_chunk($iva_month, 3) as $v) {

            $iva_trimestre[] = array_reduce($v, function ($sum, $item){

                $sum += $item;

                return $sum;

            });

        }

//        dd($iva_trimestre);

        /**
         * Inserisco i dati in Google Sheets
         */
        $range = env('GOOGLE_SHEETS_YEAR') . '!' . $k_tot_month_attivo[0] . ':' . $k_tot_month_passivo[count($k_tot_month_passivo) - 1];

        $values = [
            array_values($tot_month_attivo),
            array_values($tot_month_passivo)
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

        dd($result);
    }
}
