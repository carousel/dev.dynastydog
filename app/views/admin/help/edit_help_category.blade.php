@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Help Category</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/help/help/category/edit', $helpCategory->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-helpcategory-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $helpCategory->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-helpcategory-title" class="col-sm-2 control-label">Title</label>
        <div class="col-sm-10">
            <input type="text" name="title" class="form-control" id="cp-helpcategory-title" value="{{{ Input::old('title', $helpCategory->title) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-helpcategory-parent" class="col-sm-2 control-label">Parent</label>
        <div class="col-sm-10">
            <select name="parent" class="form-control" id="cp-helpcategory-parent">
                <option value="">None</option>
                @foreach($parentHelpCategories as $parentHelpCategory)
                    <option value="{{ $parentHelpCategory->id }}" {{ (Input::old('parent', $helpCategory->parent_id) == $parentHelpCategory->id) ? 'selected' : '' }}>{{ $parentHelpCategory->title }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-helpcategory-helppages" class="col-sm-2 control-label">Help Pages</label>
        <div class="col-sm-10">
            <select name="help_pages[]" class="form-control" id="cp-helpcategory-helppages" multiple size="10">
                @foreach($helpPages as $helpPage)
                    <option value="{{ $helpPage->id }}" {{ (in_array($helpPage->id, (array)Input::old('help_pages', $helpCategory->pages()->lists('id')))) ? 'selected' : '' }}>{{ $helpPage->title }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/help/help/category/delete', $helpCategory->id) }}" name="delete_help_category" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this help category?');">Delete</a>
            <button type="submit" name="edit_help_category" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@stop
