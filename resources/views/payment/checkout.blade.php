@extends('../layouts.app-blank')

@section('content')

    <style>

        .text-gray {
            color: #888;
        }
        .table .text-small {
            font-size: 12px;
            color: #888;
        }
        .table-total-container {
            background-color: #f5f5f5;
            border: 1px solid #28a745;
            color: #28a745;
        }
        .table-total-container .text-small {
            color: #28a745;
            font-weight: bold;
        }
        .table .text-big {
            font-size: 1.4em;
        }
        .table tbody .text-right {
            white-space: nowrap;
        }

    </style>

    <script type="text/javascript">

        window.onload = function () {

            function paymentCheck(Obj) {

                var color = 'success';

                $('.alert.border-' + color)
                    .removeClass('border-' + color)
                    .removeClass('text-' + color);

                Obj.closest('.alert')
                    .addClass('border-' + color)
                    .addClass('text-' + color);

            }

            var ObjPayment = $('[name="payment"]');

            ObjPayment.each(function () {

                var Obj = $(this);

                if(Obj.prop('checked')) {
                    paymentCheck(Obj);
                }

            });

            ObjPayment.on('change', function () {

                paymentCheck($(this));

            });

        };

    </script>

    <div class="container">

        <br>

        <h1 class="text-center">Conferma il tuo rinnovo.</h1>

        <div class="text-center">
            Il rinnovo è relativo al servizio <strong>{{ $customer_service->name }}</strong>  di <strong>{{ $customer_service->reference }}</strong>
        </div>

        <br>

        <div class="row">
            <div class="col-lg-7">

                <div class="card">

                    <div class="card-header">
                        I tuoi dati
                    </div>
                    <div class="card-body">

                        <div class="form-group">
                            <label for="nome">Azienda</label>
                            <input type="text"
                                   class="form-control"
                                   id="nome"
                                   @if($cliente['nome'])
                                   value="{{ $cliente['nome'] }}"
                                   @endif
                                   readonly>
                        </div>

                        <div class="form-group">
                            <label for="indirizzo_via">Indirizzo</label>
                            <input type="text"
                                   class="form-control"
                                   id="indirizzo_via"
                                   @if($cliente['indirizzo_via'])
                                   value="{{ $cliente['indirizzo_via'] }}"
                                   @endif
                                   readonly>
                        </div>

                        <div class="row">
                            <div class="col-lg-3">

                                <div class="form-group">
                                    <label for="indirizzo_cap">CAP</label>
                                    <input type="text"
                                           class="form-control"
                                           id="indirizzo_cap"
                                           @if($cliente['indirizzo_cap'])
                                           value="{{ $cliente['indirizzo_cap'] }}"
                                           @endif
                                           readonly>
                                </div>

                            </div>
                            <div class="col-lg-7">

                                <div class="form-group">
                                    <label for="indirizzo_citta">Città</label>
                                    <input type="text"
                                           class="form-control"
                                           id="indirizzo_citta"
                                           @if($cliente['indirizzo_citta'])
                                           value="{{ $cliente['indirizzo_citta'] }}"
                                           @endif
                                           readonly>
                                </div>

                            </div>
                            <div class="col-lg-2">

                                <div class="form-group">
                                    <label for="indirizzo_provincia">Prov.</label>
                                    <input type="text"
                                           class="form-control"
                                           id="indirizzo_provincia"
                                           @if($cliente['indirizzo_provincia'])
                                           value="{{ $cliente['indirizzo_provincia'] }}"
                                           @endif
                                           readonly>
                                </div>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="paese">Paese</label>
                            <input type="text"
                                   class="form-control"
                                   id="paese"
                                   @if($cliente['paese'])
                                   value="{{ $cliente['paese'] }}"
                                   @endif
                                   readonly>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">

                                <div class="form-group">
                                    <label for="piva">P.IVA</label>
                                    <input type="text"
                                           class="form-control"
                                           id="piva"
                                           @if($cliente['piva'])
                                           value="{{ $cliente['piva'] }}"
                                           @endif
                                           readonly>
                                </div>

                            </div>
                            <div class="col-lg-6">

                                <div class="form-group">
                                    <label for="cf">C.F.</label>
                                    <input type="text"
                                           class="form-control"
                                           id="cf"
                                           @if($cliente['cf'])
                                           value="{{ $cliente['cf'] }}"
                                           @endif
                                           readonly>
                                </div>

                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="mail">Email</label>
                            <input type="text"
                                   class="form-control"
                                   id="mail"
                                   @if($cliente['mail'])
                                   value="{{ $cliente['mail'] }}"
                                   @endif
                                   readonly>
                        </div>

                    </div>

                </div>

                <br>

            </div>
            <div class="col-lg-5">

                <div class="card">

                    <div class="card-header">
                        {{ $customer_service->name }} <strong>{{ $customer_service->reference }}</strong>
                    </div>
                    <div class="card-body">

                        <table class="table table-sm">
                            <thead class="text-small">
                            <tr>
                                <th>Servizio</th>
                                <th class="text-center"></th>
                                <th class="text-right">Importo</th>
                            </tr>
                            </thead>

                            <tbody class="text-small">

                            @php($services_total = 0)
                            @php($discount_total = 0)
                            @php($services_discount_total = 0)

                            @foreach($array_services_rows as $k => $v)

                                @if($v['price_customer_sell'] >= $v['price_sell'])
                                    @php($service_price = $v['price_customer_sell'])
                                @else
                                    @php($service_price = $v['price_sell'])
                                @endif

                                @php($discount = 0)
                                @php($discount_alert = 0)
                                @if($v['price_sell'] > $v['price_customer_sell'] && $v['is_share'] != 1)
                                    @php($discount_alert = 1)
                                    @php($discount = $v['price_sell'] - $v['price_customer_sell'])
                                @endif

                                <tr>
                                    <td>
                                        {{ $k }}
                                    </td>
                                    <td class="text-center">
                                        <small>
                                            @if(count($v['reference']) > 1)
                                                {{ count($v['reference']) }} x &euro; {{ number_format($service_price, 2, ',', '.') }}
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-right">
                                        @if($discount_alert == 1)
                                            *
                                        @endif
                                        &euro; {{ number_format((count($v['reference']) * $service_price), 2, ',', '.') }}
                                    </td>
                                </tr>

                                @php($services_total += $service_price * count($v['reference']))
                                @php($discount_total += $discount)

                            @endforeach

                            @if($discount_total > 0)
                                <tr>
                                    <td>
                                        @php($discount_per = $discount_total/$services_total*100)
                                        Sconto incondizionato

                                        @if($discount_per >= 10)
                                            (<strong>
                                                {{ number_format($discount_per, 2, ',', '.') }}%
                                            </strong>)
                                        @endif
                                    </td>
                                    <td colspan="2" class="text-right">
                                        <strong>- &euro; {{ number_format($discount_total, 2, ',', '.') }}</strong>
                                    </td>
                                </tr>
                            @endif

                            </tbody>
                        </table>

                        @php($total = $services_total - $discount_total)

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

                        <small class="text-gray">* servizi ai quali è stato applicato uno sconto.</small>

                    </div>

                </div>

                <br>

                <div class="card border-success">
                    <div class="card-header bg-success border-success text-white">

                        Metodo di pagamento

                    </div>
                    <div class="card-body">

                        <div class="alert text-secondary">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="bonifico" name="payment" class="custom-control-input" checked>
                                <label class="custom-control-label" for="bonifico">Bonifico</label>
                            </div>
                        </div>

                        <div class="alert text-secondary">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="paypal" name="payment" class="custom-control-input">
                                <label class="custom-control-label" for="paypal">PayPal / Carta di Credito</label>
                            </div>
                        </div>

                        <button class="btn btn-success btn-lg btn-block">
                            Concludi ordine
                        </button>

                    </div>
                </div>

                <br>

            </div>
        </div>

    </div>

@endsection
