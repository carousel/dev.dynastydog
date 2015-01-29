@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Symptom</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/health/symptom/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-symptom-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-symptom-name" value="{{{ Input::old('name') }}}" required />
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_symptom" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop
