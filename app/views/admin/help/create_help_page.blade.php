@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Help Page</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/help/help/page/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-helppage-titles" class="col-sm-2 control-label">Title</label>
        <div class="col-sm-10">
            <input type="text" name="title" class="form-control" id="cp-helppage-title" value="{{{ Input::old('title') }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-helppage-content" class="col-sm-2 control-label">Content</label>
        <div class="col-sm-10">
            <textarea name="content" class="form-control" id="cp-helppage-content" rows="10" required>{{{ Input::old('content') }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_help_page" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop
