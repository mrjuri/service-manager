<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
        $alpha = array('B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M');

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
        $ndc_attive = $fic->get(
            'ndc',
            'lista',
            array(
                'anno' => env('GOOGLE_SHEETS_YEAR'),
                'data_inizio' => '01/01/' . env('GOOGLE_SHEETS_YEAR'),
                'data_fine' => '31/12/' . env('GOOGLE_SHEETS_YEAR')
            )
        );

        // Fattura in cloud unice Fatture e Note di credito solo per i documenti passivi,
        // per i documenti attivi li divice, quindi utilizzo array_merge.
        $fatture_attive = array_merge($fatture_attive, $ndc_attive);

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

            // Calcolo tasse (IVA) a debito per mese
            if (!isset($iva_debito[$month_n])) {
                $iva_debito[$month_n] = 0;
            }

            // Correggo l'importo attivo in base alle note di credito attive
            switch ($fattura['tipo']) {
                case 'fatture':
                    $tot_month_attivo[$range_array[$month_n]['in']] += $fattura['importo_netto'];

                    // Verifica se PA per Split Payment
                    if ($fattura['PA_tipo_cliente'] != 'PA') {
                        $iva_debito[$month_n] += $fattura['importo_totale'] - $fattura['importo_netto'];
                    }
                    break;

                case 'ndc':
                    $tot_month_attivo[$range_array[$month_n]['in']] -= $fattura['importo_netto'];

                    // Verifica se PA per Split Payment
                    if ($fattura['PA_tipo_cliente'] != 'PA') {
                        $iva_debito[$month_n] -= $fattura['importo_totale'] - $fattura['importo_netto'];
                    }
                    break;
            }

        }

        // Sistemo l'array dei totali per importarli correttamente in Google Sheets
        $tot_month_attivo = array_reverse($tot_month_attivo, true);

        // - - -

        $tot_month_passivo = array();
        $iva_credito = array();

        foreach ($fatture_passive as $fattura) {

            // Calcolo importo netto in passivo per mese
            $month_n = date('n', strtotime(str_replace('/', '-', $fattura['data'])));

            if (!isset($tot_month_passivo[$range_array[$month_n]['out']])) {
                $tot_month_passivo[$range_array[$month_n]['out']] = 0;
            }

            // Calcolo tasse (IVA) a credito per mese
            if (!isset($iva_credito[$month_n])) {
                $iva_credito[$month_n] = 0;
            }

            // Correggo l'importo passivo in base alle note di credito passive
            switch ($fattura['tipo']) {
                case 'spesa':
                    $tot_month_passivo[$range_array[$month_n]['out']] -= $fattura['importo_netto'];
                    $iva_credito[$month_n] += $fattura['importo_iva'];
                    break;

                case 'ndc':
                    $tot_month_passivo[$range_array[$month_n]['out']] += $fattura['importo_netto'];
                    $iva_credito[$month_n] -= $fattura['importo_iva'];
                    break;
            }

        }

        // Sistemo l'array dei totali per importarli correttamente in Google Sheets
        $tot_month_passivo = array_reverse($tot_month_passivo, true);

        // - - -

        /**
         * Allineamento Keys dei totali attivi e passivi per mese
         */
        if (count($tot_month_attivo) > count($tot_month_passivo)) {

            $count_m = count($tot_month_attivo);

        } else {

            $count_m = count($tot_month_passivo);
        }

        for ($i = 0; $i < $count_m; $i++) {

            if (!isset($tot_month_attivo[$range_array[$i + 1]['in']])) {
                $tot_month_attivo[$range_array[$i + 1]['in']] = 0;
            }

            if (!isset($tot_month_passivo[$range_array[$i + 1]['out']])) {
                $tot_month_passivo[$range_array[$i + 1]['out']] = 0;
            }

        }

        // Sistemo l'array Keys dei totali per importarli correttamente in Google Sheets
        $k_tot_month_attivo = array_keys($tot_month_attivo);
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

        // Verifico la data finale del trimestre e la confronto con la data attuale
        // se la data attuale supera il trimestre di 10 giorni, viene azzerato il
        // valore IVA del trimestre, in questo modo non viene inserito il valore.
        //
        // Questo viene fatto perché se il commercialista comunica un importo
        // diverso di IVA da versare, quest'ultimo non verrà sovrascritto.
        foreach (array_chunk($iva_month, 3, true) as $k => $v) {

            $keys = array_keys($v);

            $data_fine_trimestre = date(
                'Y-m-t',
                mktime(0, 0, 0, $keys[count($keys) - 1], 1, env('GOOGLE_SHEETS_YEAR'))
            );

            $date_trimestre = new Carbon($data_fine_trimestre);
            $date_now = Carbon::now();

            if ($date_trimestre->diff($date_now)->days > 10 &&
                $date_trimestre->timestamp < $date_now->timestamp) {
                $iva_trimestre[$k] = 0;
            }

        }

        /**
         * Inserisco i dati in Google Sheets
         */
        $data = array();

        // Range delle fatture attive e passive
        $range = env('GOOGLE_SHEETS_YEAR') . '!' . $k_tot_month_attivo[0] . ':' . $k_tot_month_passivo[count($k_tot_month_passivo) - 1];
        $values = [
            array_values($tot_month_attivo),
            array_values($tot_month_passivo)
        ];
        $data[] = new \Google_Service_Sheets_ValueRange([
            'range' => $range,
            'values' => $values
        ]);

        // Range dei trimestri IVA da versare
        foreach (array_chunk($alpha, 3) as $k => $v) {

            // Verifico che il valore del trimestre esista e che sia maggiore di zero,
            // perché calcolo l'IVA da versare fino ad una settimana dopo la fine del
            // trimestre, in questo modo se il commercialista comunica un importo
            // diverso di IVA da versare, quest'ultimo non verrà sovrascritto.
            if (isset($iva_trimestre[$k]) && abs($iva_trimestre[$k]) > 0) {

                $range = env('GOOGLE_SHEETS_YEAR') . '!' . $v[0] . '7';
                $values = [
                    [$iva_trimestre[$k]]
                ];
                $data[] = new \Google_Service_Sheets_ValueRange([
                    'range' => $range,
                    'values' => $values
                ]);
            }
        }

        $client = $this->getClient();
        $service = new \Google_Service_Sheets($client);
        $spreadsheetId = env('GOOGLE_SHEETS_ID');

        $body = new \Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => 'RAW',
            'data' => $data
        ]);

        $result = $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);

        /*$body = new \Google_Service_Sheets_ValueRange([
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
        );*/

        $this->scriptableJSON();
    }

    public function scriptableJSON()
    {
        $alpha = array('B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M');

        $row_in = 3;
        $row_out = 4;
        $row_profit = 16;

        $client = $this->getClient();
        $service = new \Google_Service_Sheets($client);
        $spreadsheetId = env('GOOGLE_SHEETS_ID');

        $c = 0;

        foreach (array_chunk($alpha, 3) as $k => $v) {

            $c += 3;

            if ($c > date('m') - 1) {
                $periodo = $k + 1;
                $alphaUtile = $v[0];
                break;
            }
        }

        $params = array(
            'ranges' => [
                env('GOOGLE_SHEETS_YEAR') . '!' . $alpha[date('m') - 1] . $row_in,
                env('GOOGLE_SHEETS_YEAR') . '!' . $alpha[date('m') - 1] . $row_out,
                env('GOOGLE_SHEETS_YEAR') . '!' . $alphaUtile . $row_profit,
            ]
        );
        $result = $service->spreadsheets_values->batchGet($spreadsheetId, $params);

        $dataArray = array(
            'trimestre' => array(
                'periodo' => $periodo,
                'value' => $result->valueRanges[2]->values[0][0],
            ),
            'entrate' => $result->valueRanges[0]->values[0][0],
            'uscite' => $result->valueRanges[1]->values[0][0],
        );

        Storage::disk('public')->put('scriptable.json', json_encode($dataArray));
    }
}
