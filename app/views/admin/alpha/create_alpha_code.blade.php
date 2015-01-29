@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Alpha Code</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/alpha/code/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-alphacode-capacity" class="col-sm-2 control-label">Capacity</label>
        <div class="col-sm-10">
            <input type="number" min="0" name="capacity" class="form-control" id="cp-alphacode-capacity" value="{{{ Input::old('capacity') }}}" required />
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_alpha_code" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop
