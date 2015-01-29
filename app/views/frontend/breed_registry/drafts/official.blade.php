@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <div class="text-right pull-right">
        <a href="{{ route('breed_registry') }}" class="btn btn-primary">Go to the Breed Registry Home</a>
    </div>

    <h1>List of In-Progress Real Breeds</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-official-breed-drafts">
            <div class="form-group">
                <label for="search-official-breed-drafts-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-official-breed-drafts-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="official" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $results->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Status</th>
            <th>Edited</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $breedDraft)
        @if($breedDraft->isExtinct())
        <tr class="warning">
        @elseif($breedDraft->isRejected())
        <tr class="danger">
        @elseif($breedDraft->isAccepted())
        <tr class="success">
        @elseif($breedDraft->isPending())
        <tr class="info">
        @else
        <tr>
        @endif
            <td>{{{ $breedDraft->name }}}</td>
            <td>{{ $breedDraft->getStatus() }}</td>
            <td>{{ $breedDraft->updated_at->format('M jS, Y g:i A') }}</td>
        </tr>
        @endforeach

        @if($results->isEmpty())
        <tr>
            <td colspan="3">No drafts to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $results->appends(array_except(Input::all(), 'page'))->links() }}

@stop
