@extends('../layouts.app-blank')

@section('content')

    <style>

        .mrj-logo-container .row div {
            padding: 0 !important;
        }
        .mrj-bg-red {
            background-image: url("{{ asset('assets/img/bg-red.png') }}");
            background-size: contain;
            height: 100%;
            width: 100%;
        }
        .mrj-logo {
            height: 80px;
        }

        @media only screen and (max-width: 1000px) {

            .mrj-logo {
                width: 98%;
                height: auto;
            }

        }

    </style>

    <br>

    <div class="container-fluid mrj-logo-container">

        <div class="row">
            <div class="col-lg-6">

                <div class="mrj-bg-red"></div>

            </div>
            <div class="col-lg-4">

                <img src="{{ asset('assets/img/mrj-logo.png') }}" alt="Mr. J - Logo" class="mrj-logo">

            </div>
        </div>

    </div>

    <br>

    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-2"></div>
            <div class="col-lg-8">

                <h1>
                    Lorem ipsum dolor sit amet
                </h1>

                <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                </p>

                <h2>
                    Lorem ipsum dolor sit amet
                </h2>

                <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                </p>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                </p>

            </div>
            <div class="col-lg-2"></div>
        </div>

    </div>

@endsection
