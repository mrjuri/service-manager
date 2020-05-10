@extends('../layouts.app-blank')

@section('content')

    <style>

        .order-details label {
            text-transform: uppercase;
            font-size: 12px;
        }
        .order-fields > div {
            border-left: 1px dashed #888;
        }
        .order-fields > div:first-child {
            border-left: 0;
        }

    </style>

    <div class="container">

        <br>

        <h1 class="text-center">Il tuo rinnovo è confermato.</h1>

        <div class="text-center">
            Rinnovo relativo al servizio <strong>{{ $service->name }}</strong>  di <strong>{{ $service->reference }}</strong>
        </div>

        <br><br><br>

        <h2 class="text-center" style="font-size: 3.2em; color: #f00;">Grazie.</h2>

        <br><br><br>

        <hr>

        <br>

        <div class="order-details">

            <div class="row">
                <div class="col-lg-3">

                    <label>Numero ordine:</label>
                    <br>
                    <strong>
                        {{ date('Ymd', strtotime($payment->payment_date)) }}-{{ substr($payment->sid, 0, 2) }}{{ substr($payment->sid, -2, 2) }}
                    </strong>
                    <br><br>

                </div>
                <div class="col-lg-3">

                    <label>Data:</label>
                    <br>
                    <strong>
                        {{ date('d/m/Y', strtotime($payment->payment_date)) }}
                        {{ date('h:i', strtotime($payment->payment_date)) }}
                    </strong>
                    <br><br>

                </div>
                <div class="col-lg-3">

                    <label>Totale:</label>
                    <br>
                    <strong>
                        &euro; {{ number_format($payment->amount * 1.22, 2, ',', '.') }}
                    </strong>
                    <br><br>

                </div>
                <div class="col-lg-3">

                    <label>Metodo di pagamento:</label>
                    <br>
                    <strong>
                        {{ ucfirst($payment->type) }}
                    </strong>
                    <br><br>

                </div>
            </div>

            <hr>
            <br>

            <div class="row">
                <div class="col-lg-6">

                    <table class="table table-sm table-borderless">
                        <thead class="text-small">
                        <tr>
                            <th>Servizio</th>
                            <th class="text-center"></th>
                            <th class="text-right">Importo</th>
                        </tr>
                        </thead>

                        <tbody class="text-small">

                        @php($services_total = 0)

                        @foreach($array_services_rows as $k => $v)

                            <tr>
                                <td>
                                    {{ $k }}
                                </td>
                                <td class="text-center">
                                    <small>
                                        @if(count($v['reference']) > 1)
                                            {{ count($v['reference']) }} x &euro; {{ number_format($v['price_sell'], 2, ',', '.') }}
                                        @endif
                                    </small>
                                </td>
                                <td class="text-right">
                                    &euro; {{ number_format((count($v['reference']) * $v['price_sell']), 2, ',', '.') }}
                                </td>
                            </tr>

                            @php($services_total += $v['price_sell'] * count($v['reference']))

                        @endforeach

                        </tbody>
                    </table>

                    @php($total = $services_total)

                    <table class="table table-sm table-borderless table-total-container">

                        <tr class="text-small">
                            <td>Imponibile</td>
                            <td colspan="2" class="text-right">
                                &euro; {{ number_format($total, 2, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="text-small">
                            <td>Totale IVA</td>
                            <td colspan="2" class="text-right">
                                &euro; {{ number_format(($total * 1.22 - $total), 2, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="text-big">
                            <th colspan="3" class="text-right">
                                &euro; {{ number_format($total * 1.22, 2, ',', '.') }}
                            </th>
                        </tr>

                    </table>

                </div>
                <div class="col-lg-6">

                    @if($payment->type == 'bonifico')
                        {!! $payment_info !!}
                    @endif

                </div>
            </div>

        </div>

    </div>

@endsection
