@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Locus</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/genetics/locus/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-locus-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-locus-name" value="{{{ Input::old('name') }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-locus-active" class="col-sm-2 control-label">Active?</label>
        <div class="col-sm-7">
            <div class="checkbox">
                <label for="cp-locus-active">
                    <input type="checkbox" name="active" value="yes" id="cp-locus-active" {{ (Input::old('active') == 'yes') ? 'checked' : '' }} />
                    Yes
                </label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_locus" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop
