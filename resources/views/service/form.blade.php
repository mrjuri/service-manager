@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

    {{--<a href="{{ route('service.list') }}" class="btn btn-primary">Indietro</a>--}}
    <a href="javascript: history.go(-1);" class="btn btn-primary">Indietro</a>

    <br /><br />

    <form method="post"
          @if (isset($service->id))
          action="{{ route('service.update', $service->id) }}"
          @else
          action="{{ route('service.store') }}"
            @endif >

        @csrf

        <div class="row">
            <div class="col-lg-6">

                <div class="form-group">
                    <input type="text"
                           class="form-control"
                           aria-describedby="nome"
                           placeholder="Nome servizio"
                           name="name"
                           @if (isset($service->name))
                           value="{{ $service->name }}"
                            @endif />
                </div>

            </div>
            <div class="col-lg-3">

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text text-success">&euro;</span>
                    </div>
                    <input type="text"
                           class="form-control text-right text-success"
                           aria-label="Prezzo vendita"
                           placeholder="Prezzo vendita"
                           name="price_sell"
                           @if (isset($service->price_sell))
                           value="{{ $service->price_sell }}"
                            @endif />
                </div>

            </div>
            <div class="col-lg-3">

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text text-danger">&euro;</span>
                    </div>
                    <input type="text"
                           class="form-control text-right text-danger"
                           aria-label="Prezzo acquisto"
                           placeholder="Prezzo acquisto"
                           name="price_buy"
                           @if (isset($service->price_buy))
                           value="{{ $service->price_buy }}"
                            @endif />
                </div>

            </div>
        </div>

        <div class="form-group form-check">
            <input type="checkbox"
                   class="form-check-input"
                   id="shareCheck"
                   name="is_share"
                   value="1"
                   @if (isset($service->is_share) && $service->is_share == 1)
                   checked
                    @endif />

            <label class="form-check-label" for="shareCheck">Servizio condiviso</label>
        </div>

        <button type="submit" class="btn btn-primary">Salva</button>
    </form>

    @if (isset($service->name))

        <hr>

        <h2>Clienti che utilizzano questo servizio:</h2>

        <table class="table table-hover">
            <thead>
            <tr>
                <th>Cliente</th>
                <th class="text-right" style="width: 120px;">Entrate</th>
                <th class="text-right" style="width: 120px;">Uscite</th>
                <th class="text-right" style="width: 120px;">Utile</th>
                <th style="width: 80px;"></th>
            </tr>
            </thead>
            <tbody>

            @foreach($customers as $k => $customer)

                <tr>
                    <td class="btn-row align-middle" data-toggle="collapse" data-target="#details-{{ $customer->id }}">

                        {{ $customer->company }}
                        <br />
                        <small>
                            {{ $customer->name }} - {{ $customer->email }}
                        </small>

                    </td>
                    <td class="text-right text-success">

                        @if($customer->price_sell > 0)
                        &euro; {{ number_format($customer->price_sell, 2, ',', '.') }}
                        @endif

                    </td>
                    <td class="text-right text-danger">

                        @if($customer->price_buy > 0)

                        &euro; {{ number_format($customer->price_buy, 2, ',', '.') }}

                        @else

                            🤑🤑🤑

                        @endif

                    </td>
                    <td class="text-right {{ $customer->price_utile < 0 ? 'text-danger' : '' }}">

                        <strong>
                            &euro; {{ number_format($customer->price_utile, 2, ',', '.') }}
                        </strong>

                        <br />

                        @if($customer->per_utile != 0)
                            <small class="{{ $customer->per_utile < 0 ? 'text-danger' : '' }}">
                                R. {{ number_format($customer->per_utile, 2, ',', '.') }}%
                            </small>
                        @endif

                    </td>
                    <td class="align-middle text-right">

                        <a href="{{ route('customer.edit', $customer->id) }}" class="btn btn-dark">
                            <i class="far fa-edit"></i>
                        </a>

                    </td>
                </tr>

                @foreach($services_details as $k => $services_detail)

                    @if($services_detail->customer_id == $customer->id)

                        <tr>
                            <td colspan="5" style="padding: 0; margin: 0; border: 0; background-color: #fff;">

                                <div class="collapse" id="details-{{ $services_detail->customer_id }}">

                                    <table class="table-borderless" style="width: 100%;">
                                        <tr>
                                            <td style="padding-left: 40px;">
                                                <div style="width: 100px; float: left;">
                                                    scadenza
                                                    <br>
                                                    <small>
                                                        {{ date('d/m/Y', strtotime($services_detail->expiration)) }}
                                                    </small>
                                                </div>
                                                <div>
                                                    {{ $services_detail->name }} {{ $services_detail->reference_service }}
                                                    <br>
                                                    <strong>
                                                        {{ $services_detail->reference_detail }}
                                                    </strong>
                                                </div>

                                            </td>
                                            <td class="text-right text-success" style="width: 120px;">

                                                <small>
                                                    @if($services_detail->price_sell > 0)
                                                    &euro; {{ number_format($services_detail->price_sell, 2, ',', '.') }}
                                                    @endif
                                                </small>

                                            </td>
                                            <td class="text-right text-danger" style="width: 120px;">

                                                <small>
                                                    @if($services_detail->price_buy > 0)
                                                    &euro; {{ number_format($services_detail->price_buy, 2, ',', '.') }}
                                                    @else

                                                        🤑🤑🤑

                                                    @endif
                                                </small>

                                            </td>
                                            <td class="text-right" style="width: 120px;">

                                                <small>
                                                    <strong>
                                                        @if($services_detail->price_utile > 0)
                                                            &euro; {{ number_format($services_detail->price_utile, 2, ',', '.') }}
                                                        @elseif($services_detail->price_utile == 0)
                                                            -
                                                        @endif
                                                    </strong>

                                                    <br />

                                                    @if($services_detail->per_utile != 0)
                                                        <small class="{{ $services_detail->per_utile < 0 ? 'text-danger' : '' }}">
                                                            R. {{ number_format($services_detail->per_utile, 2, ',', '.') }}%
                                                        </small>
                                                    @endif
                                                </small>

                                            </td>
                                            <td style="width: 80px;"></td>
                                        </tr>
                                    </table>

                                </div>

                            </td>
                        </tr>

                    @endif

                @endforeach

            @endforeach
            </tbody>
        </table>

    @endif

@endsection
