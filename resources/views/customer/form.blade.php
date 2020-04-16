@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

    <script language="JavaScript">

        window.onload = function() {

            $('body').on('click', '.btn-service-new', function () {

                var $Obj = $(this);
                var $ObjContainer = $Obj.closest('.service');
                var $serviceHTML = '<div class="service">' + $ObjContainer.html() + "</div>";

                $Obj.removeClass('btn-service-new').addClass('btn-service-del');
                $Obj.html('<i class="fas fa-minus"></i>');

                $ObjContainer.parent().find('.service').last().after($serviceHTML);

                /**
                 * Imposto le variabili con Array bidimensionali
                 */
                $('.service').each(function (i) {

                    $(this).find('.service-details-input').each(function (i_details) {

                        var $Obj = $(this);
                        var $name = $Obj.attr('name').split('[');

                        $Obj.attr('name', $name[0] + '[' + i + '][]');

                    });

                });

                return false;

            });

            $('body').on('click', '.btn-service-del', function () {

                $(this).closest('.service').remove();

                return false;

            });

            $('body').on('click', '.btn-service-details-new', function () {

                var $Obj = $(this);
                var $ObjContainer = $Obj.closest('.service-details');
                var $serviceDetailHTML = '<div class="service-details">' + $ObjContainer.html() + "</div>";

                $Obj.removeClass('btn-service-details-new').addClass('btn-service-details-del');
                $Obj.html('<i class="fas fa-minus"></i>');

                $ObjContainer.parent().find('.service-details').last().after($serviceDetailHTML);

                return false;

            });

            $('body').on('click', '.btn-service-details-del', function () {

                $(this).closest('.service-details').remove();

                return false;

            });

            $('body').on('change', '.service-details-option', function () {

                var $Obj = $(this);
                var $price_advice = $Obj.find(':selected').attr('data-price_sell');

                if ($price_advice == 0) $price_advice = '';

                $Obj.closest('.service-details').find('.price_sell').val($price_advice);

                return false;

            });

        }

    </script>

{{--    <a href="{{ route('customer.list') }}" class="btn btn-primary">Indietro</a>--}}
    <a href="javascript: history.go(-1);" class="btn btn-primary">Indietro</a>

    <br /><br />

    <form method="post"
          autocomplete="off"
          @if (isset($customer->id))
          action="{{ route('customer.update', $customer->id) }}"
          @else
          action="{{ route('customer.store') }}"
            @endif >

        @csrf

        <div class="card bg-secondary text-white">
            <div class="card-header">

                <div class="row">
                    <div class="col-lg-5">

                        <input type="text"
                               class="form-control"
                               aria-describedby="company"
                               placeholder="Ragione sociale cliente"
                               name="company"
                               @if (isset($customer->company))
                               value="{{ $customer->company }}"
                            @endif />

                    </div>
                    <div class="col-lg-4">

                        <input type="email"
                               class="form-control"
                               aria-describedby="email"
                               placeholder="Email cliente"
                               name="email"
                               @if (isset($customer->email))
                               value="{{ $customer->email }}"
                            @endif />

                    </div>
                    <div class="col-lg-3">

                        <input type="text"
                               class="form-control"
                               aria-describedby="nome"
                               placeholder="Nome cliente"
                               name="name"
                               @if (isset($customer->name))
                               value="{{ $customer->name }}"
                            @endif />

                    </div>
                </div>

            </div>
        </div>

        <br>

        @foreach($customersServices as $k => $customerService)

            <div class="service">

                <div class="card">

                    <div class="card-header">

                        <div class="row">
                            <div class="col-lg-5">

                                <input type="text"
                                       class="form-control"
                                       aria-describedby="service_company"
                                       placeholder="Azienda di riferimento"
                                       name="service_company[]"
                                       @if (isset($customerService->company))
                                       value="{{ $customerService->company }}"
                                    @endif />

                            </div>
                            <div class="col-lg-4">

                                <input type="text"
                                       class="form-control"
                                       aria-describedby="service_email"
                                       placeholder="Email di avviso"
                                       name="service_email[]"
                                       @if (isset($customerService->email))
                                       value="{{ $customerService->email }}"
                                    @endif />

                            </div>
                            <div class="col-lg-3">

                                <input type="text"
                                       class="form-control"
                                       aria-describedby="service_customer_name"
                                       placeholder="Nome di avviso"
                                       name="service_customer_name[]"
                                       @if (isset($customerService->customer_name))
                                       value="{{ $customerService->customer_name }}"
                                    @endif />

                            </div>
                        </div>

                    </div>

                    <div class="card-body">

                        <div class="row">
                            <div class="col-lg-5">

                                <input type="text"
                                       class="form-control"
                                       aria-describedby="service_name"
                                       placeholder="Nome del servizio"
                                       name="service_name[]"
                                       @if (isset($customerService->name))
                                       value="{{ $customerService->name }}"
                                        @endif />

                            </div>
                            <div class="col-lg-4">

                                <input type="text"
                                       class="form-control"
                                       aria-describedby="service_name"
                                       placeholder="Riferimento servizio"
                                       name="service_reference[]"
                                       @if (isset($customerService->reference))
                                       value="{{ $customerService->reference }}"
                                        @endif />

                            </div>
                            <div class="col-lg-2">

                                <input type="text"
                                       class="form-control text-center"
                                       aria-describedby="service_expiration"
                                       placeholder="dd/mm/yyyy"
                                       name="service_expiration[]"
                                       @if (isset($customerService->expiration))
                                       value="{{ date('d/m/Y', strtotime($customerService->expiration)) }}"
                                        @endif />

                            </div>
                            <div class="col-lg-1">

                                @if ($k < count($customersServices) - 1)
                                    <button class="btn btn-primary btn-block btn-dark btn-service-del">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                @else
                                    <button class="btn btn-primary btn-block btn-dark btn-service-new">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endif

                            </div>
                        </div>

                        <br />

                        @foreach($customerService->details as $k_detail => $detail)

                            <div class="service-details">

                                <div class="row">
                                    <div class="col-lg-5">

                                        <div class="form-group">
                                            <select class="form-control custom-select service-details-input service-details-option"
                                                    name="service_details[{{ $k }}][]">
                                                <option value="">- - -</option>
                                                @foreach($services as $service)
                                                    <option value="{{ $service->id }}"
                                                            data-price_sell="{{ $service->price_sell }}"
                                                            @if (isset($detail->service_id) && $detail->service_id == $service->id)
                                                            selected
                                                            @endif >
                                                        {{ $service->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>
                                    <div class="col-lg-4">

                                        <div class="form-group">
                                            <input type="text"
                                                   class="form-control service-details-input"
                                                   aria-describedby="reference"
                                                   placeholder="Riferimento dettaglio"
                                                   name="service_details_reference[{{ $k }}][]"
                                                   @if (isset($detail->reference))
                                                   value="{{ $detail->reference }}"
                                                    @endif />
                                        </div>

                                    </div>
                                    <div class="col-lg-2">

                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">&euro;</span>
                                            </div>
                                            <input type="text"
                                                   autocomplete="off"
                                                   class="form-control text-right price_sell service-details-input"
                                                   aria-describedby="price_sell"
                                                   name="service_details_price_sell[{{ $k }}][]"
                                                   @if (isset($detail->price_sell))
                                                   value="{{ $detail->price_sell }}"
                                                    @endif />
                                        </div>

                                    </div>
                                    <div class="col-lg-1">

                                        @if ($k_detail < count($customerService->details) - 1)
                                            <button class="btn btn-primary btn-block btn-service-details-del">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-primary btn-block btn-service-details-new">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endif

                                    </div>
                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

                <br />

            </div>

        @endforeach

        <button type="submit" class="btn btn-primary">Salva</button>
    </form>

@endsection
