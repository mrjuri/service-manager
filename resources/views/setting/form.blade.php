@extends('../layouts.app')
@extends('../layouts.breadcrumb')

@section('content')

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
                   aria-selected="false">Email</a>

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

                <textarea name="email_body" id="editor"></textarea>

                <script>
                    ClassicEditor
                        .create( document.querySelector( '#editor' ) )
                        .catch( error => {
                        console.error( error );
                    } );
                </script>

                <br>
                <small>campo_1</small>

            </div>
        </div>

        <br>
        <button type="submit" class="btn btn-primary">Salva</button>
    </form>

@endsection
