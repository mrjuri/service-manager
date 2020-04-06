@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @if(count(Request::segments()) > 0)

            @foreach(Request::segments() as $k => $segment)
                <li class="breadcrumb-item {{ ($k + 1 == count(Request::segments())) ? 'active' : '' }}">
                    @if(($k + 1 == count(Request::segments())))

                        @if(is_numeric($segment))
                            @php($controllerName = Request::segment(1))
                            {{ ucfirst(__('breadcrumb.name', ['name' => $$controllerName->name])) }}
                        @else
                            {{ ucfirst(__('breadcrumb.' . $segment)) }}
                        @endif

                    @else
                        <a href="#">{{ ucfirst(__('breadcrumb.' . $segment)) }}</a>
                    @endif
                </li>
            @endforeach

        @else

            <li class="breadcrumb-item active">
                Dashboard
            </li>

        @endif
    </ol>
</nav>
@endsection