<div class="row">
    <div class="col-lg-6">

        <div class="card">
            <div class="card-body">

                <table class="table custom">
                    <tbody>
                    <tr>
                        <td rowspan="2" class="text-center align-middle">

                            <h2>
                                &euro; {{ number_format($totals['price_utile'], 2, ',', '.') }}
                            </h2>

                        </td>
                        <td>

                            <div class="row">
                                <div class="col-lg-6">
                                    Entrate
                                </div>
                                <div class="col-lg-6 text-right text-success">
                                    &euro; {{ number_format($totals['price_sell'], 2, ',', '.') }}
                                </div>
                            </div>

                        </td>
                    </tr>
                    <tr>
                        <td>

                            <div class="row">
                                <div class="col-lg-6">
                                    Uscite
                                </div>
                                <div class="col-lg-6 text-right text-danger">
                                    &euro; {{ number_format($totals['price_buy'], 2, ',', '.') }}
                                </div>
                            </div>

                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
    <div class="col-lg-3">

        <div class="card">
            <div class="card-body">

                <table class="table custom">
                    <tbody>
                    <tr>
                        <td class="text-center align-middle">
                            <strong>
                                {{ count($customers) }}
                            </strong>
                        </td>
                        <td class="align-middle">
                            Clienti attivi
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center align-middle">
                            <strong>
                                {{ $customersServicesActive }}
                            </strong>
                        </td>
                        <td class="align-middle">
                            Abbonamenti attivi
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
    <div class="col-lg-3">

        @if($s)
            <div class="card alert-info">
                <div class="card-body">

                    <table class="table custom">
                        <tbody>
                        <tr>
                            <td class="text-center align-middle">
                                <strong>
                                    {{ count($customersServices) }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center align-middle">
                                Abbonamenti trovati
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body">

                    <table class="table custom">
                        <tbody>
                        <tr>
                            <td class="text-center align-middle">
                                <strong>
                                    &euro; {{ number_format($totals['price_utile'] / count($customers), 2, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center align-middle">
                                di media a cliente
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        @endif

    </div>
</div>

<br />

<table class="table table-hover">
    <thead>
    <tr>
        <th>Servizio</th>
        <th class="text-right">Entrate</th>
        <th class="text-right">Uscite</th>
        <th class="text-right">Utile</th>
    </tr>
    </thead>

    <tbody>

    @php($total_utile = 0)
    @foreach($servicesList as $service)

        <tr>
            <td class="align-middle">
                {{ $service->name }}
            </td>
            <td class="align-middle text-right text-success">

                &euro; {{ number_format($service->price_sell, 2, ',', '.') }}

                <br />

                <small class="text-dark">
                    (
                    {{ $service->customers_n }}
                    x
                    {{ number_format($service->price_sell / $service->customers_n, 2, ',', '.') }}
                    )
                </small>

            </td>
            <td class="align-middle text-right {{ $service->price_buy > 0 ? 'text-danger' : '' }}">

                @if($service->price_buy > 0)

                &euro; {{ number_format($service->price_buy, 2, ',', '.') }}

                <br />

                <small class="text-dark">
                    (
                    {{ $service->customers_n }}
                    x
                    {{ number_format($service->price_buy / $service->customers_n, 2, ',', '.') }}
                    )
                </small>

                @else

                    🤑🤑🤑

                @endif

            </td>
            <td class="text-right align-middle">

                @php($total_utile += $service->price_utile)
                <strong>
                    &euro; {{ number_format($service->price_utile, 2, ',', '.') }}
                </strong>

            </td>
        </tr>

    @endforeach

    </tbody>
    <tfoot>
    <tr>
        <td colspan="4" class="text-right">
            <strong>
                &euro; {{ number_format($total_utile, 2, ',', '.') }}
            </strong>
        </td>
    </tr>
    </tfoot>
</table>