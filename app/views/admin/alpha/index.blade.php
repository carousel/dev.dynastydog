@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Alpha Codes</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-alpha-codes">
            <div class="form-group">
                <label for="search-alpha-codes-code" class="col-sm-2 control-label">Code</label>
                <div class="col-sm-10">
                    <input type="text" name="code" class="form-control" id="search-alpha-codes-code" value="{{{ Input::get('code') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="alpha_codes" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $alphaCodes->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>Code</th>
            <th>Capacity</th>
            <th>Population</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        @foreach($alphaCodes as $alphaCode)
        <tr>
            <td><a href="{{ route('admin/alpha/code/edit', $alphaCode->code) }}">{{ $alphaCode->code }}</a></td>
            <td>{{ $alphaCode->capacity }}</td>
            <td>{{ $alphaCode->population }}</td>
            <td>{{ $alphaCode->created_at->format('F j, Y g:i A') }}</td>
        </tr>
        @endforeach()

        @if($alphaCodes->isEmpty())
        <tr>
            <td colspan="3">No alpha codes to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $alphaCodes->appends(array_except(Input::all(), 'page'))->links() }}

@stop
