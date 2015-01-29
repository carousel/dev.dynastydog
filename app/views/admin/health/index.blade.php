@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Symptoms</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-symptoms">
            <div class="form-group">
                <label for="search-symptoms-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-symptoms-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-symptoms-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-symptoms-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="symptoms" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $symptoms->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
        </tr>
    </thead>
    <tbody>
        @foreach($symptoms as $symptom)
        <tr>
            <td><a href="{{ route('admin/health/symptom/edit', $symptom->id) }}">{{ $symptom->id }}</a></td>
            <td><a href="{{ route('admin/health/symptom/edit', $symptom->id) }}">{{ $symptom->name }}</a></td>
        </tr>
        @endforeach()

        @if($symptoms->isEmpty())
        <tr>
            <td colspan="2">No symptoms to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $symptoms->appends(array_except(Input::all(), 'page'))->links() }}

@stop
