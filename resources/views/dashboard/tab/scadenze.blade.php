<script language="JavaScript">

    window.onload = function() {

        $('.modal').on('show.bs.modal', function(e) {
            $(this).find('#customer_service_id').val($(e.relatedTarget).data('id'));
        });

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })

    };

</script>

<table class="table table-hover">
    <thead>
    <tr>
        <th style="width: 180px;"></th>
        <th>Cliente <small>(hai {{ count($customersServices) }} scadenze)</small></th>
        <th>Servizio</th>
        <th class="text-center">Scadenza</th>
        <th class="text-right">Importo</th>
    </tr>
    </thead>

    <tbody>

    @foreach($customersServices as $customersService)

        @php($className = '')

        @if(date('YmdHis', strtotime('+2 month')) > date('YmdHis', strtotime($customersService->expiration)))
            @php($className = 'table-warning')
            @php($btnClassName = 'btn-warning')
        @endif

        @if(date('YmdHis') > date('YmdHis', strtotime($customersService->expiration)))
            @php($className = 'table-danger text-danger')
            @php($btnClassName = 'btn-danger')
        @endif

        <tr class="{{ $className }}">
            <td class="text-center align-middle">

                @if(date('YmdHis', strtotime('+2 month')) > date('YmdHis', strtotime($customersService->expiration)))
                    <div class="row">
                        <div class="col-lg-4">
                            <button type="button"
                                    class="btn btn-block btn-sm btn-modal {{ $btnClassName }}"
                                    data-toggle="modal"
                                    data-target="#renewModal"
                                    data-href="{{ route('customer.renew', $customersService->id) }}">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div class="col-lg-4">
                            <button type="button"
                                    class="btn btn-block btn-sm btn-modal {{ $btnClassName }}"
                                    data-toggle="modal"
                                    data-target="#sendAlertModal"
                                    data-href="{{ route('email.exp', $customersService->id) }}">
                                <i class="fas fa-at"></i>
                            </button>
                        </div>
                        <div class="col-lg-4">
                            <button type="button"
                                    class="btn btn-block btn-sm btn-modal @if($customersService->payment_type)
                                    btn-success
                                    @else
                                    {{ $btnClassName }}
                                    @endif"
                                    data-toggle="modal"
                                    data-target="#invoiceModal"
                                    data-id="{{ $customersService->id }}">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </button>
                        </div>
                    </div>
                @endif
            </td>
            <td class="btn-row" data-toggle="collapse" data-target="#details-{{ $customersService->id }}">
                @if($customersService->company)
                    {{ Str::limit($customersService->company, 35, '...') }}
                @else
                    {{ Str::limit($customersService->customer->company, 35, '...') }}
                @endif
                <br />
                <small>
                    @if($customersService->customer_name)
                        {{ $customersService->customer_name }}
                    @else
                        {{ $customersService->customer->name }}
                    @endif
                    -
                    @if($customersService->email)
                        @php($email_array = explode(';', $customersService->email))
                    @else
                        @php($email_array = explode(';', $customersService->customer->email))
                    @endif

                    {{ $email_array[0] }}

                    @if(count($email_array) > 1)
                        <span class="badge badge-primary"
                              data-toggle="tooltip"
                              data-html="true"
                              title="@foreach($email_array as $k => $email)
                              @if($k > 0)
                              {{ $email }}<br>
                              @endif
                              @endforeach">
                            cc
                        </span>
                    @endif
                </small>
            </td>
            <td class="align-middle btn-row" data-toggle="collapse" data-target="#details-{{ $customersService->id }}">
                {{ $customersService->name }}
                <br />
                <small>
                    {{ $customersService->reference }}
                </small>
            </td>
            <td class="align-middle text-center btn-row" data-toggle="collapse" data-target="#details-{{ $customersService->id }}">
                {{ date('d/m/Y', strtotime($customersService->expiration)) }}
            </td>
            <td class="align-middle text-right" data-toggle="collapse" data-target="#details-{{ $customersService->id }}">

                @php($detail_total = 0)
                @foreach($customersService->details as $detail)

                    @if(isset($detail->service))
                    @php($detail_total += $detail->price_sell)
                    @endif

                @endforeach

                @if($detail_total != 0)
                    <strong>
                        &euro; {{ number_format($detail_total, 2, ',', '.') }}
                    </strong>
                    <br>
                    <small>
                        &euro; {{ number_format($detail_total * 1.22, 2, ',', '.') }}
                    </small>
                @endif

            </td>
        </tr>

        <tr>
            <td colspan="5" style="padding: 0; margin: 0; border: 0; background-color: #fff;">

                <div class="collapse" id="details-{{ $customersService->id }}">

                    <table class="table-borderless" style="width: 100%;">

                        <tbody>

                        @foreach($customersService->details as $detail)

                            @if(isset($detail->service))

                                <tr class="{{ $className }}">
                                    <th style="width: 180px;"></th>
                                    <td>

                                        {{ $detail->service->name }}
                                        <br />
                                        <small>
                                            {{ $detail->reference }}
                                        </small>

                                    </td>
                                    <td class="text-right align-middle">

                                        &euro; {{ number_format($detail->price_sell, 2, ',', '.') }}

                                    </td>
                                </tr>

                            @endif

                        @endforeach

                        </tbody>

                    </table>

                </div>

            </td>
        </tr>

    @endforeach

    </tbody>

</table>

<!-- Modal -->
<div class="modal fade" id="renewModal" tabindex="-1" role="dialog" aria-labelledby="renewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renewModalLabel">Rinnova</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Rinnovare il servizio?

                <br /><br />

                <div class="row">
                    <div class="col-lg-6">

                        <a href="#" class="btn btn-success btn-block btn-confirm">Sì</a>

                    </div>
                    <div class="col-lg-6">

                        <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal">No</button>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="sendAlertModal" tabindex="-1" role="dialog" aria-labelledby="sendAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendAlertModalLabel">Avvisa il cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Vuoi inviare un avviso via email al cliente?

                <br /><br />

                <div class="row">
                    <div class="col-lg-6">

                        <a href="#" class="btn btn-success btn-block btn-confirm">Sì</a>

                    </div>
                    <div class="col-lg-6">

                        <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal">No</button>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">Genera fattura</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <br><br>

                <div class="text-center">
                    Vuoi <strong>inviare la fattura</strong> al cliente?
                </div>

                <br><br>

                <hr>

                <form action="{{ route('fattureincloud.api.create') }}" method="post">

                    @csrf

                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               class="custom-control-input"
                               id="paymentSwitch"
                               name="pagamento_saldato"
                               value="1"
                               checked>
                        <label class="custom-control-label" for="paymentSwitch">
                            Pagamento ricevuto.
                            <br>
                            <small>(se lo switch è attivo, la fattura risulterà saldata)</small>
                        </label>
                    </div>

                    <hr>

                    <small>
                        <strong>Nota:</strong>
                        <br>
                        Generando la fattura rinnoverai automaticamente il servizio al prossimo anno.
                    </small>

                    <hr>

                    <div class="row">
                        <div class="col-lg-6">

                            <button type="submit" class="btn btn-success btn-block btn-confirm">Sì</button>

                            <input type="hidden" name="customer_service_id" id="customer_service_id" value="">

                        </div>
                        <div class="col-lg-6">

                            <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal">No</button>

                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
