@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

    <style>
        .dynamic_field {
            margin-bottom: 5px;
        }
    </style>
    {{--<a href="javascript: history.go(-1);" class="btn btn-primary">Indietro</a>

    <br /><br />--}}

    <br>

    <form method="post"
          @if (isset($settings->id))
          action="{{ route('setting.update', $settings->id) }}"
          @else
          action="{{ route('setting.store') }}"
            @endif >

        @csrf

        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">

                <a class="nav-item nav-link active"
                   id="nav-mail-tab"
                   data-toggle="tab"
                   href="#nav-mail"
                   role="tab"
                   aria-controls="nav-mail"
                   aria-selected="false">Email Scadenza</a>

                <a class="nav-item nav-link"
                   id="nav-mail-exp-tab"
                   data-toggle="tab"
                   href="#nav-mail-exp"
                   role="tab"
                   aria-controls="nav-mail-exp"
                   aria-selected="false">Email Scaduto</a>

            </div>
        </nav>

        <br />

        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-mail" role="tabpanel" aria-labelledby="nav-mail-tab">

                <div class="row">
                    <div class="col-lg-6">

                        <div class="form-group">
                            <input type="text"
                                   class="form-control"
                                   aria-describedby="nome"
                                   placeholder="Nome mittente"
                                   name="email_name_sender"
                                   @if (isset($settings->email_name_sender))
                                   value="{{ $settings->email_name_sender }}"
                                    @endif />
                        </div>

                    </div>
                    <div class="col-lg-6">

                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">@</span>
                            </div>
                            <input type="text"
                                   class="form-control"
                                   aria-label="Email mittente"
                                   placeholder="Email mittente"
                                   name="email_mail_sender"
                                   @if (isset($settings->email_mail_sender))
                                   value="{{ $settings->email_mail_sender }}"
                                    @endif />
                        </div>

                    </div>
                </div>

                <div class="form-group">
                    <input type="text"
                           class="form-control"
                           aria-describedby="Oggetto"
                           placeholder="Oggetto"
                           name="email_subject"
                           @if (isset($settings->email_subject))
                           value="{{ $settings->email_subject }}"
                            @endif />
                </div>

                <div class="row">
                    <div class="col-lg-3">

                        <div class="card" style="margin-bottom: 15px;">
                            <div class="card-header">
                                Campi dinamici
                            </div>
                            <div class="card-body">

                                <button class="btn btn-outline-primary btn-sm dynamic_field" data-dyn="[customers-company]">Ragione sociale</button>
                                <button class="btn btn-outline-primary btn-sm dynamic_field" data-dyn="[customers-name]">Nome cliente</button>

                                <button class="btn btn-outline-primary btn-sm dynamic_field" data-dyn="[customers_services-name]">Servizio cliente nome</button>
                                <button class="btn btn-outline-primary btn-sm dynamic_field" data-dyn="[customers_services-reference]">Servizio cliente rif.</button>
                                <button class="btn btn-outline-primary btn-sm dynamic_field" data-dyn="[customers_services-expiration]">Servizio cliente exp.</button>
                                <button class="btn btn-outline-primary btn-sm dynamic_field" data-dyn="[customers_services-list_]">Servizio cliente lista</button>
                                <button class="btn btn-outline-primary btn-sm dynamic_field" data-dyn="[customers_services-total_]">Servizio cliente tot.</button>

                                <button class="btn btn-outline-primary btn-sm dynamic_field" data-dyn="[service-name]">Servizio nome</button>
                                <button class="btn btn-outline-primary btn-sm dynamic_field" data-dyn="[service-price_sell]">Servizio prezzo</button>

                            </div>
                        </div>

                    </div>
                    <div class="col-lg-9">

                        <textarea name="email_body" id="editor">@if (isset($settings->email_body))
                                value="{{ $settings->email_body }}"
                            @endif</textarea>

                    </div>
                </div>

                <script>
                    ClassicEditor
                        .create( document.querySelector( '#editor' ) )
                        .then( editor => { window.editor = editor; } )
                        .catch( error => {
                        console.error( error );
                    } );
                </script>

                <script>

                    $('.dynamic_field').on('click', function () {

                        window.editor.model.change( writer => {
                            const insertPosition = editor.model.document.selection.getFirstPosition();
                            writer.insertText( $(this).attr('data-dyn'), insertPosition );
                        } );

                        return false;

                    });

                </script>

            </div>
            <div class="tab-pane fade show active" id="nav-mail-exp" role="tabpanel" aria-labelledby="nav-mail-exp-tab">
                expirated
            </div>
        </div>

        <br>
        <button type="submit" class="btn btn-primary">Salva</button>
    </form>

@endsection
