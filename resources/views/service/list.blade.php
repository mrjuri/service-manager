@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

    <script language="JavaScript">

        window.onload = function() {

            $('#deleteModal').on('show.bs.modal', function(e) {
                $(this).find('#btn-del').attr('href', $(e.relatedTarget).data('href'));
            });

        };

    </script>

    <div class="row">
        <div class="col-lg-6">

            <a href="{{ route('service.create') }}" class="btn btn-primary">Nuovo</a>

        </div>
        <div class="col-lg-6">

            <div class="float-right">
                <form class="form-inline my-2 my-lg-0" action="{{ route('service.list') }}" method="get">

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
            <th>Nome</th>
            <th class="text-center">Share</th>
            <th class="text-right">Entrate</th>
            <th class="text-right">Uscite</th>
            <th class="text-right">Utile</th>
            <th class="text-right">% Ut.Tot.</th>
            <th></th>
        </tr>
        </thead>
        <tbody>

        @foreach($services as $service)

            <tr>
                <td class="align-middle">

                    {{ $service->name }}

                    <br />

                    <small>
                        venduti <strong>{{ $service->n_servizi_venduti }}</strong>
                    </small>

                </td>
                <td class="align-middle text-center">

                    @if($service->is_share)
                        <i class="fas fa-share-alt"></i>
                    @endif

                </td>
                <td class="text-right text-success">

                    &euro; {{ number_format($service->price_sell, 2, ',', '.') }}

                </td>
                <td class="text-right text-danger">

                    @if($service->price_buy > 0)

                    &euro; {{ number_format($service->price_buy, 2, ',', '.') }}

                    @else

                        🤑🤑🤑

                    @endif

                </td>
                <td class="text-right">

                    <strong>
                        &euro; {{ number_format($service->price_utile, 2, ',', '.') }}
                    </strong>

                    <br />

                    <small data-toggle="tooltip"
                           data-placement="top"
                           title="Percentuale ricarico">
                        R.
                        @if($service->price_buy != 0)
                            {{ number_format($service->per_utile, 2, ',', '.') }}%
                        @else
                            ♾
                        @endif
                    </small>

                </td>
                <td class="align-middle text-right {{ $service->per < 0 ? 'text-danger' : '' }}">

                    &euro; {{ number_format($service->price_utile_totale, 2, ',', '.') }}

                    <br />

                    <small>
                        {{ number_format($service->per, 2, ',', '.') }}%
                    </small>

                </td>
                <td class="align-middle text-right">

                    <a href="{{ route('service.edit', $service->id) }}" class="btn btn-dark">
                        <i class="far fa-edit"></i>
                    </a>

                    <button type="button"
                            class="btn btn-dark"
                            data-toggle="modal"
                            data-target="#deleteModal"
                            data-href="{{ route('service.destroy', $service->id) }}">
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
