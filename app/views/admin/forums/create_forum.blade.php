@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Forum</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/forums/forum/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-forum-title" class="col-sm-2 control-label">Title</label>
        <div class="col-sm-10">
            <input name="title" class="form-control" id="cp-forum-title" value="{{{ Input::old('title') }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-forum-description" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
            <textarea name="description" class="form-control" id="cp-forum-description" rows="3">{{{ Input::old('description') }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_forum" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop
