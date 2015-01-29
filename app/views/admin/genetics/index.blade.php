@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Loci</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-loci">
            <div class="form-group">
                <label for="search-loci-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-loci-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-loci-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-loci-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="loci" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $loci->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Active?</th>
        </tr>
    </thead>
    <tbody>
        @foreach($loci as $locus)
        <tr>
            <td><a href="{{ route('admin/genetics/locus/edit', $locus->id) }}">{{ $locus->id }}</a></td>
            <td><a href="{{ route('admin/genetics/locus/edit', $locus->id) }}">{{ $locus->name }}</a></td>
            <td><big>{{ $locus->isActive() ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>' }}</big></td>
        </tr>
        @endforeach()

        @if($loci->isEmpty())
        <tr>
            <td colspan="3">No loci to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $loci->appends(array_except(Input::all(), 'page'))->links() }}

@stop
