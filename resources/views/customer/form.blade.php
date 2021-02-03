@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

    <script language="JavaScript">

        window.onload = function() {

            /**
             * Modal delete alert
             */
            $('#deleteModal').on('show.bs.modal', function(e) {
                $(this).find('#btn-del').attr('href', $(e.relatedTarget).data('href'));
            });

            /**
             * Imposto le variabili con Array bidimensionali
             */
            function fixIndex()
            {
                $('.service').each(function (i) {

                    $(this).find('.service-details-input').each(function (i_details) {

                        var $Obj = $(this);
                        var $name = $Obj.attr('name').split('[');

                        $Obj.attr('name', $name[0] + '[' + i + '][]');

                    });

                });
            }

            $('body').on('click', '.btn-service-new', function () {

                var $Obj = $(this);
                var $ObjContainer = $Obj.closest('.service');
                var $serviceHTML = '<div class="service">' + $ObjContainer.html() + "</div>";

                $Obj.removeClass('btn-service-new').addClass('btn-service-del');
                $Obj.html('<i class="fas fa-minus"></i>');

                $ObjContainer.parent().find('.service').last().after($serviceHTML);

                /**
                 * Rimuovo gli ID
                 */
                $('.service').last().find('.service_id').remove();
                $('.service').last().find('.service_details_id').remove();

                fixIndex();

                return false;

            });

            $('body').on('click', '.btn-service-del', function () {

                $(this).closest('.service').remove();

                fixIndex();

                return false;

            });

            $('body').on('click', '.btn-service-details-new', function () {

                var $Obj = $(this);
                var $ObjContainer = $Obj.closest('.service-details');
                var $serviceDetailHTML = '<div class="service-details">' + $ObjContainer.html() + "</div>";

                $Obj.removeClass('btn-service-details-new').addClass('btn-service-details-del');
                $Obj.removeClass('btn-primary').addClass('btn-secondary');
                $Obj.html('<i class="fas fa-minus"></i>');

                $ObjContainer.parent().find('.service-details').last().after($serviceDetailHTML);

                /**
                 * Rimuovo gli ID
                 */
                $ObjContainer.parent().find('.service-details').last().find('.service_details_id').remove();

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

    <a href="{{ route('customer.list') }}{{ app('request')->session()->get('sc') ? '/?s=' . app('request')->session()->get('sc') : '' }}"
       class="btn btn-primary">Indietro</a>
{{--    <a href="javascript: history.go(-1);" class="btn btn-primary">Indietro</a>--}}

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
                    <div class="col-lg-4">

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
                    <div class="col-lg-2">

                        <input type="text"
                               class="form-control"
                               aria-describedby="nome"
                               placeholder="Nome cliente"
                               name="name"
                               @if (isset($customer->name))
                               value="{{ $customer->name }}"
                            @endif />

                    </div>
                    <div class="col-lg-2">

                        <input type="text"
                               class="form-control"
                               aria-describedby="piva"
                               placeholder="p.iva"
                               name="piva"
                               @if (isset($customer->piva))
                               value="{{ $customer->piva }}"
                            @endif />

                    </div>
                </div>

            </div>
        </div>

        <br>

        @foreach($customersServices as $k => $customerService)

            <div class="service">

                @if(isset($customerService->id))
                    <div class="service_id">
                        <input type="hidden" name="service_id[]" value="{{ $customerService->id }}">
                    </div>
                @endif

                <div class="card border-warning">

                    <div class="card-header bg-warning">

                        <div class="row">
                            <div class="col-lg-4">

                                <input type="text"
                                       class="form-control"
                                       aria-describedby="service_company"
                                       @if (isset($customer->company))
                                       placeholder="{{ $customer->company }}"
                                       @else
                                       placeholder="Azienda di riferimento"
                                       @endif
                                       name="service_company[]"
                                       @if (isset($customerService->company))
                                       value="{{ $customerService->company }}"
                                    @endif />

                            </div>
                            <div class="col-lg-3">

                                <input type="text"
                                       class="form-control"
                                       aria-describedby="service_email"
                                       @if (isset($customer->email))
                                       placeholder="{{ $customer->email }}"
                                       @else
                                       placeholder="Email di avviso"
                                       @endif
                                       name="service_email[]"
                                       @if (isset($customerService->email))
                                       value="{{ $customerService->email }}"
                                    @endif />

                            </div>
                            <div class="col-lg-2">

                                <input type="text"
                                       class="form-control"
                                       aria-describedby="service_customer_name"
                                       @if (isset($customer->name))
                                       placeholder="{{ $customer->name }}"
                                       @else
                                       placeholder="Nome di avviso"
                                       @endif
                                       name="service_customer_name[]"
                                       @if (isset($customerService->customer_name))
                                       value="{{ $customerService->customer_name }}"
                                    @endif />

                            </div>
                            <div class="col-lg-2">

                                <input type="text"
                                       class="form-control"
                                       aria-describedby="service_piva"
                                       @if (isset($customer->piva))
                                       placeholder="{{ $customer->piva }}"
                                       @else
                                       placeholder="p.iva"
                                       @endif
                                       name="service_piva[]"
                                       @if (isset($customerService->piva))
                                       value="{{ $customerService->piva }}"
                                    @endif />
                                {{--<a class="btn btn-warning btn-block"
                                   target="_blank"
                                   href="{{ route('email.exp', [$customer->id, $customerService->id]) }}">
                                    <i class="fas fa-at"></i>
                                </a>--}}

                            </div>
                            <div class="col-lg-1">

                                <button type="button"
                                        class="btn btn-block btn-danger"
                                        data-toggle="modal"
                                        data-target="#deleteModal"
                                        data-href="{{ route('customer.service.destroy', $customerService->id) }}/?cid={{ $customer->id }}">
                                    <i class="far fa-trash-alt"></i>
                                </button>

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

                                @if(isset($detail->id))
                                    <div class="service_details_id">
                                        <input type="hidden" class="service-details-input" name="service_details_id[{{ $k }}][]" value="{{ $detail->id }}">
                                    </div>
                                @endif

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
                                            <button class="btn btn-secondary btn-block btn-service-details-del">
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

                        <div class="form-group form-check" style="margin-bottom: 0;">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="singleVoice"
                                   name="single_voice[]"
                                   value="1"
                                   @if (isset($service->single_voice) && $service->single_voice == 1)
                                   checked
                                @endif />

                            <label class="form-check-label" for="singleVoice">
                                <small>Voce unica in fattura</small>
                            </label>
                        </div>

                    </div>

                </div>

                <br />

            </div>

        @endforeach

        <button type="submit" class="btn btn-primary">Salva</button>
    </form>

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
