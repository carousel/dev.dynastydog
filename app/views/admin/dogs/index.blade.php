@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Dogs</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-dogs">
            <div class="form-group">
                <label for="search-dogs-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-dogs-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-dogs-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-dogs-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="dogs" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $dogs->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Breed</th>
            <th>Sex</th>
            <th>Owner</th>
            <th>Alive?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dogs as $dog)
        <tr>
            <td><a href="{{ route('admin/dogs/dog/edit', $dog->id) }}">{{ $dog->id }}</a></td>
            <td><a href="{{ route('admin/dogs/dog/edit', $dog->id) }}">{{{ $dog->name }}}</a></td>
            <td>
                @if($dog->hasBreed())
                <a href="{{ route('admin/breeds/breed/edit', $dog->breed->id) }}">{{{ $dog->breed->name }}}</a>
                @else
                <em>Unregistered</em>
                @endif
            </td>
            <td>
                @if($dog->hasSex())
                {{{ $dog->sex->name }}}
                @else
                <em>Unregistered</em>
                @endif
            </td>
            <td>
                @if($dog->hasOwner())
                <a href="{{ route('admin/users/user/edit', $dog->owner->id) }}">{{{ $dog->owner->display_name }}} (#{{ $dog->owner->id }})</a>
                @else
                <em>None</em>
                @endif
            </td>
            <td>{{ $dog->isAlive() ? 'Yes' : 'No' }}</td>
        </tr>
        @endforeach()

        @if($dogs->isEmpty())
        <tr>
            <td colspan="6">No dogs to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $dogs->appends(array_except(Input::all(), 'page'))->links() }}

@stop
