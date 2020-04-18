<script language="JavaScript">

    window.onload = function() {

        $('#renewModal').on('show.bs.modal', function(e) {
            $(this).find('#btn-del').attr('href', $(e.relatedTarget).data('href'));
        });

    };

</script>

<table class="table table-hover">
    <thead>
    <tr>
        <th style="width: 120px;"></th>
        <th>Cliente</th>
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
        @endif

        @if(date('YmdHis') > date('YmdHis', strtotime($customersService->expiration)))
            @php($className = 'table-danger text-danger')
        @endif

        <tr class="{{ $className }}">
            <td class="text-center align-middle">

                @if(date('YmdHis', strtotime('+2 month')) > date('YmdHis', strtotime($customersService->expiration)))
                    <button type="button"
                            class="btn btn-dark"
                            data-toggle="modal"
                            data-target="#renewModal"
                            data-href="{{ route('customer.renew', $customersService->id) }}">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <a class="btn btn-dark"
                       target="_blank"
                       href="{{ route('email.exp', [$customersService->customer->id, $customersService->id]) }}">
                        <i class="fas fa-at"></i>
                    </a>
                @endif
            </td>
            <td class="btn-row" data-toggle="collapse" data-target="#details-{{ $customersService->id }}">
                {{ $customersService->customer->company }}
                <br />
                <small>
                    @if($customersService->company)
                    {{ $customersService->company }}
                    @else
                    {{ $customersService->customer->company }}
                    @endif
                    -
                    @if($customersService->customer_name)
                    {{ $customersService->customer_name }}
                    @else
                    {{ $customersService->customer->name }}
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
                                    <th style="width: 120px;"></th>
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

                        <a href="#" id="btn-del" class="btn btn-success btn-block">Sì</a>

                    </div>
                    <div class="col-lg-6">

                        <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal">No</button>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
