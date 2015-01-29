@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Locus</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/genetics/locus/edit', $locus->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-locus-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $locus->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-locus-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-locus-name" value="{{{ Input::old('name', $locus->name) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-locus-active" class="col-sm-2 control-label">Active?</label>
        <div class="col-sm-7">
            <div class="checkbox">
                <label for="cp-locus-active">
                    <input type="checkbox" name="active" value="yes" id="cp-locus-active" {{ (Input::old('active', ($locus->active ? 'yes' : 'no')) == 'yes') ? 'checked' : '' }} />
                    Yes
                </label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/genetics/locus/delete', $locus->id) }}" name="delete_locus" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this locus?');">Delete</a>
            <button type="submit" name="edit_locus" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@stop
