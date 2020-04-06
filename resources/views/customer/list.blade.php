@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

    <style>

        .table.custom {
            margin: 0;
        }

    </style>

    <script language="JavaScript">

        window.onload = function() {

            $('#deleteModal').on('show.bs.modal', function(e) {
                $(this).find('#btn-del').attr('href', $(e.relatedTarget).data('href'));
            });

        };

    </script>

    <div class="row">
        <div class="col-lg-6">

            <a href="{{ route('customer.create') }}" class="btn btn-primary">Nuovo</a>
            <a href="{{ route('customer.create') }}" class="btn btn-primary">Filtro</a>

        </div>
        <div class="col-lg-6">

            <div class="float-right">
                <form class="form-inline my-2 my-lg-0" action="{{ route('customer.list') }}" method="get">

                    <input class="form-control mr-sm-2"
                           type="search"
                           placeholder="Cosa vuoi cercare?"
                           aria-label="Search"
                           name="s"
                           value="{{ $s }}" />

                    <button class="btn btn-outline-info my-2 my-sm-0" type="submit">Cerca</button>

                </form>
            </div>

        </div>
    </div>

    <br />

    <table class="table table-hover">
        <thead>
        <tr>
            <th>Cliente</th>
            <th class="text-right">Entrate</th>
            <th class="text-right">Uscite</th>
            <th class="text-right">Utile</th>
            <th class="text-center">% Ut.Tot.</th>
            <th></th>
        </tr>
        </thead>
        <tbody>

        @foreach($customers as $k => $customer)

            <tr class="{{ $k < 3 ? 'alert-warning' : '' }}">
                <td class="align-middle">
                    {{ $customer->company }} {{ $k < 3 ? '⭐️' : '' }}
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

                    &euro; {{ number_format($customer->price_buy, 2, ',', '.') }}

                </td>
                <td class="text-right {{ $customer->price_utile < 0 ? 'text-danger' : '' }}">

                    <strong>
                        &euro; {{ number_format($customer->price_utile, 2, ',', '.') }}
                    </strong>

                    <br />

                    <small data-toggle="tooltip"
                           data-placement="top"
                           title="Percentuale ricarico"
                           class="{{ $customer->per_utile < 0 ? 'text-danger' : '' }}">
                        R.
                        @if($customer->per_utile != '')
                            {{ number_format($customer->per_utile, 2, ',', '.') }}%
                        @else
                            ♾
                        @endif
                    </small>

                </td>
                <td class="align-middle text-center {{ $customer->per < 0 ? 'text-danger' : '' }}">

                    {{ number_format($customer->per, 2, ',', '.') }}%

                    <br />

                    <div class="progress" style="height: 4px; margin: 10px 0;">
                        <div class="progress-bar" role="progressbar" style="width: {{ number_format($customer->per, 2, '.', '') }}%;" aria-valuenow="{{ number_format($customer->per, 2, '.', '') }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                </td>
                <td class="align-middle text-right">

                    <a href="{{ route('customer.edit', $customer->id) }}" class="btn btn-dark">
                        <i class="far fa-edit"></i>
                    </a>

                    <button type="button"
                            class="btn btn-dark"
                            data-toggle="modal"
                            data-target="#deleteModal"
                            data-href="{{ route('customer.destroy', $customer->id) }}">
                        <i class="far fa-trash-alt"></i>
                    </button>

                </td>
            </tr>

        @endforeach
        </tbody>
    </table>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Elimina</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Confermi l'eminazione?

                    <br /><br />

                    <div class="row">
                        <div class="col-lg-6">

                            <a href="#" id="btn-del" class="btn btn-danger btn-block">Sì</a>

                        </div>
                        <div class="col-lg-6">

                            <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal">No</button>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
