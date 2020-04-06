<?php

namespace App\Http\Controllers;

use App\Model\CustomersServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Domain extends Controller
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
     * Lista dei domini in Plesk
     * La lista dei domini viene presa con un comando da Shell:
     * ls /var/www/vhost > s1domains.txt
     * @return array
     */
    public function get_PleskList()
    {
        $assetsPath = Storage::disk('public')->path('assets/');

        $s1_domains_array = file($assetsPath . 's1domains.txt');
        $s2_domains_array = file($assetsPath . 's2domains.txt');

        foreach ($s1_domains_array as $k => $domain) {

            $s1_domains_array[$k] = str_replace("\n", '', $domain);

        }

        foreach ($s2_domains_array as $k => $domain) {

            $s2_domains_array[$k] = str_replace("\n", '', $domain);

        }

        $domains_array = array_merge($s1_domains_array, $s2_domains_array);
        sort($domains_array);

        return $domains_array;
    }

    /**
     * Lista dei domini in wE2
     * @return array
     */
    public function get_DBList()
    {
        $domains_db = DB::table('WE_domains')
            ->select('domain')
            ->orderBy('domain')
            ->get();

        foreach ($domains_db as $domain_d) {

            $domains_db_array[] = $domain_d->domain;

        }

        return $domains_db_array;
    }

    /**
     * Estrapolo i domini che non sono dentro nel DB o dentro Plesk
     */
    public function get_noSyncDomainsList()
    {
        $plesk_list = $this->get_PleskList();
        $db_list = $this->get_DBList();

        if (count($plesk_list) >= count($db_list)) {

            $big_array = $plesk_list;
            $little_array = $db_list;

            echo '<strong>Domini non presenti in wE2</strong><br />';

        } else if (count($db_list) > count($plesk_list)) {

            $big_array = $db_list;
            $little_array = $plesk_list;

            echo '<strong>Domini non presenti in Plesk</strong><br />';

        }

        foreach ($big_array as $domain_b) {

            if (!in_array($domain_b, $little_array)) {

                echo $domain_b . "<br />";

            }

        }
    }

    /**
     * Estrapolo la lista dei domini con il guadagno
     * @return array
     */
    public function get_DBPriceList()
    {
        $domains = $this->get_PleskList();

        foreach ($domains as $domain) {

            $domain_db = CustomersServices::where('reference', $domain)->count();

            if ($domain_db < 1)
            {
                echo $domain . '<br />';
            }

        }

        $domains_db = DB::table('WE_domains')
//            ->select('domain', 'service')
            ->orderBy('domain')
            ->get();

//        dd($domains_db);

        $tot_in = 0;
        $tot_out = 0;

        echo '<table>';

        echo '<tr>
            <td></td>
            <td align="center">Guadagni</td>
            <td align="center">Spese</td>
        </tr>';

        $c = 0;

        foreach ($domains_db as $domain) {

            $c++;

            echo '<tr>';

            echo '<td style="border: 1px solid #000;">';
            echo '<strong>' . $c . ' ' . $domain->domain . ' ' . date('d/m/Y', strtotime($domain->date_exp)) . '</strong><br />';

            $domain_tot_in = 0;
            $domain_tot_out = 0;

            if ($domain->service != '') {

                $service_rows = explode('<br>', $domain->service);

                echo '<table width="100%">';
                foreach ($service_rows as $service_row) {

                    $service_data = explode(';', $service_row);

                    echo '<tr>';
                    echo '<td>' . $service_data[0] . '</td>';
                    echo '<td align="right">' . number_format($service_data[2], 2, ',', '.') . '</td>';
                    echo '</tr>';

                    $domain_tot_in += $service_data[1] * $service_data[2];

                    if (strstr(strtolower($service_data[0]), '.com')) {
                        $domain_tot_out -= 9.9;
                    }
                    if (strstr(strtolower($service_data[0]), '.it')) {
                        $domain_tot_out -= 6.99;
                    }

                }
                echo '</table>';

            }

            echo '</td>';

            $tot_in += $domain_tot_in;
            $tot_out += $domain_tot_out;

            echo '<td valign="bottom" style="text-align: right; border-bottom: 1px solid #000;">' . number_format($domain_tot_in, 2, ',', '.') . '</td>';
            echo '<td valign="bottom" style="text-align: right; border-bottom: 1px solid #000;">' . number_format($domain_tot_out, 2, ',', '.') . '</td>';

            echo '</tr>';

        }

        echo '<tr>
            <td></td>
            <td style="text-align: right;">
            <hr />
            ' . number_format($tot_in, 2, ',', '.') . '
            </td>
            <td style="text-align: right;">
            <hr />
            ' . number_format($tot_out, 2, ',', '.') . '
            </td>
        </tr>';

        echo '</table>';
    }

    public function get_list()
    {
        $this->get_DBPriceList();
    }
}
