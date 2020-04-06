@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

    <style>

        .table.custom {
            margin: 0;
        }
        .table.custom td {
            border: none;
        }
        .btn-row {
            cursor: pointer;
        }

    </style>

    <div class="row">
        <div class="col-lg-6">



        </div>
        <div class="col-lg-6">

            <div class="float-right">
                <form class="form-inline my-2 my-lg-0" action="{{ route('home') }}" method="get">

                    <input class="form-control mr-sm-2"
                           type="search"
                           placeholder="Servizio o cliente"
                           aria-label="Search"
                           name="s"
                           value="{{ $s }}" />

                    <button class="btn btn-outline-info my-2 my-sm-0" type="submit">Cerca</button>

                </form>
            </div>

        </div>
    </div>

    <br />

    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">

            <a class="nav-item nav-link active"
               id="nav-scadenze-tab"
               data-toggle="tab"
               href="#nav-scadenze"
               role="tab"
               aria-controls="nav-scadenze"
               aria-selected="false">Scadenze</a>

            <a class="nav-item nav-link"
               id="nav-guardagno_mese-tab"
               data-toggle="tab"
               href="#nav-guardagno_mese"
               role="tab"
               aria-controls="nav-guardagno_mese"
               aria-selected="false">Guadagno per mese</a>

            <a class="nav-item nav-link"
               id="nav-dashboard-tab"
               data-toggle="tab"
               href="#nav-dashboard"
               role="tab"
               aria-controls="nav-dashboard"
               aria-selected="true">Utile</a>

        </div>
    </nav>

    <br />

    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-scadenze" role="tabpanel" aria-labelledby="nav-scadenze-tab">

            @include('dashboard.tab.scadenze')

        </div>

        <div class="tab-pane fade show" id="nav-guardagno_mese" role="tabpanel" aria-labelledby="nav-guardagno_mese-tab">

            @include('dashboard.tab.guadagno_per_mese')

        </div>

        <div class="tab-pane fade show" id="nav-dashboard" role="tabpanel" aria-labelledby="nav-dashboard-tab">

            @include('dashboard.tab.utile')

        </div>
    </div>

@endsection
