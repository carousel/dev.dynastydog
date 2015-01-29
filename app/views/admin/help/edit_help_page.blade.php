@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Help Page</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/help/help/page/edit', $helpPage->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-helppage-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $helpPage->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-helppage-title" class="col-sm-2 control-label">Title</label>
        <div class="col-sm-10">
            <input type="text" name="title" class="form-control" id="cp-helppage-title" value="{{{ Input::old('title', $helpPage->title) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-helppage-content" class="col-sm-2 control-label">Content</label>
        <div class="col-sm-10">
            <textarea name="content" class="form-control" id="cp-helppage-content" rows="10" required>{{{ Input::old('content', $helpPage->content) }}}</textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-helppage-helpcategories" class="col-sm-2 control-label">Help Categories</label>
        <div class="col-sm-10">
            <select name="help_categories[]" class="form-control" id="cp-helppage-helpcategories" multiple size="10">
                @foreach($helpCategories as $helpCategory)
                    <option value="{{ $helpCategory->id }}" {{ (in_array($helpCategory->id, (array)Input::old('help_categories', $helpPage->categories()->lists('id')))) ? 'selected' : '' }}>{{ $helpCategory->title }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/help/help/page/delete', $helpPage->id) }}" name="delete_help_page" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this help page?');">Delete</a>
            <button type="submit" name="edit_help_page" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@stop
