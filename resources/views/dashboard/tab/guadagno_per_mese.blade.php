<table class="table table-hover">
    <thead>
    <tr>
        <th>Mese</th>
        <th class="text-right">Entrate</th>
        <th class="text-right">Uscite</th>
        <th class="text-right">Utile</th>
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

        <tr class="{{ $className }}">

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
        </tr>
    </tfoot>

</table>