<script type="text/javascript">

    $(window).ready(function () {

        $('td').hover(function () {

            $Obj = $(this).closest('tr');

            $Obj.find('td[rowspan]').addClass('hover');

            if ($Obj.has('td[rowspan]').length == 0) {
                $Obj.prevAll('tr:has(td[rowspan]):first').find('td[rowspan]').addClass('hover');
            }

        }, function () {

            $Obj.find('td[rowspan]').removeClass('hover');
            $Obj.prevAll('tr:has(td[rowspan]):first').find('td[rowspan]').removeClass('hover');

        });

        $('td[rowspan]').hover(function () {

            $Obj = $(this);
            $DataTrim = $Obj.data('trim');
            $ObjRows = $Obj.closest('tbody').find('.' + $DataTrim).find('td');

            $ObjRows.addClass('hover');

        }, function () {

            $ObjRows.removeClass('hover');

        });

    });

</script>

<style>
    td {
        transition: all .3s;
    }
    .hover {
        background-color: #00ff00 !important;
    }
    td[rowspan].hover {
        background-color: #00ff00 !important;
        color: #000 !important;
        font-weight: bold;
    }
</style>

<table class="table table-hover">
    <thead>
    <tr>
        <th width="30%">Mese</th>
        <th width="17.5%" class="text-right">Entrate</th>
        <th width="17.5%" class="text-right">Uscite</th>
        <th width="17.5%" class="text-right">Utile</th>
        <th width="17.5%" class="text-center">U. Trimestre</th>
    </tr>
    </thead>
    <tbody>

    @php($price_sell_tot = 0)
    @php($price_buy_tot = 0)
    @php($price_utile_tot = 0)
    @foreach($months_services as $k => $months_service)

        @php($className = '')

        @if($k < date('n'))
            @php($className = 'table-secondary text-secondary')
        @endif

        @if($k == date('n'))
            @php($className = 'table-warning text-dark')
        @endif

        @if(!isset($m_count) || $m_count >= 3)

            @if(isset($i))
                @php($m_index = $i)
            @else
                @php($m_index = 1)
            @endif

            @php($m_count = 1)
            @php($trim_tot = 0)

        @else
            @php($m_count += 1)
        @endif

        <tr class="{{ $className }} {{ 'trim_' . $m_index }}">

            <td>
                {{ ucfirst($months_service['month']) }}
            </td>

            <td class="text-right {{ strstr($className, 'secondary') ? '' : 'text-success' }}">
                @if(isset($months_service['price_sell']))
                @php($price_sell_tot += $months_service['price_sell'])
                &euro; {{ number_format($months_service['price_sell'], 2, ',', '.') }}
                @endif
            </td>

            <td class="text-right {{ strstr($className, 'secondary') ? '' : 'text-danger' }}">
                @if(isset($months_service['price_buy']))
                @php($price_buy_tot += $months_service['price_buy'])
                &euro; {{ number_format($months_service['price_buy'], 2, ',', '.') }}
                @endif
            </td>

            <td class="text-right {{ (isset($months_service['price_utile']) && $months_service['price_utile'] < 0) ? 'text-danger' : '' }}">
                <strong>
                    @if(isset($months_service['price_utile']))
                    @php($price_utile_tot += $months_service['price_utile'])
                    &euro; {{ number_format($months_service['price_utile'], 2, ',', '.') }}
                    @endif
                </strong>
            </td>

            @if($m_count == 1)
            <td rowspan="3" style="border-left: 1px solid #b3b7bb; background-color: #fff;" class="text-center align-middle" data-trim="{{ 'trim_' . $m_index }}">

                @for($i = $m_index; $i <= ($m_index + 2); $i++)

                    @if(isset($months_services[$i]['price_utile']))
                        @php($trim_tot += number_format($months_services[$i]['price_utile'], 2, '.', ''))
                    @endif

                @endfor

                &euro; {{ number_format($trim_tot, 2, ',', '.') }}

            </td>
            @endif

        </tr>

    @endforeach

    </tbody>

    <tfoot>
        <tr>
            <td></td>
            <td class="text-right text-success">

                <strong>
                    @if(isset($price_sell_tot))
                    &euro; {{ number_format($price_sell_tot, 2, ',', '.') }}
                    @endif
                </strong>

            </td>
            <td class="text-right text-danger">

                <strong>
                    @if(isset($price_buy_tot))
                    &euro; {{ number_format($price_buy_tot, 2, ',', '.') }}
                    @endif
                </strong>

            </td>
            <td class="text-right">

                <strong>
                    @if(isset($price_utile_tot))
                    &euro; {{ number_format($price_utile_tot, 2, ',', '.') }}
                    @endif
                </strong>

                @if($s)
                    <br />
                    <small>
                        % {{ number_format(($price_utile_tot / $totals['price_utile'] * 100), 2, ',', '.') }}
                        di &euro; {{ number_format($totals['price_utile'], 2, ',', '.') }}
                    </small>
                @endif

            </td>
            <td></td>
        </tr>
    </tfoot>

</table>
