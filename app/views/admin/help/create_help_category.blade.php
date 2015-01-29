@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Help Category</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/help/help/category/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-helpcategory-title" class="col-sm-2 control-label">Title</label>
        <div class="col-sm-10">
            <input type="text" name="title" class="form-control" id="cp-helpcategory-title" value="{{{ Input::old('title') }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-helpcategory-parent" class="col-sm-2 control-label">Parent</label>
        <div class="col-sm-10">
            <select name="parent" class="form-control" id="cp-helpcategory-parent">
                <option value="">None</option>
                @foreach($parentHelpCategories as $parentHelpCategory)
                    <option value="{{ $parentHelpCategory->id }}" {{ (Input::old('parent') == $parentHelpCategory->id) ? 'selected' : '' }}>{{ $parentHelpCategory->title }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_help_category" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop
