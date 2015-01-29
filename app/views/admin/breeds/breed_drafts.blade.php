@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Breed Drafts</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-breeddrafts">
            <div class="form-group">
                <label for="search-breeddrafts-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-breeddrafts-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-breeddrafts-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-breeddrafts-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="breed-drafts" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $breedDrafts->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Submitted</th>
        </tr>
    </thead>
    <tbody>
        @foreach($breedDrafts as $breedDraft)
        <tr>
            <td><a href="{{ route('admin/breeds/breed/draft/edit', $breedDraft->id) }}">{{ $breedDraft->id }}</a></td>
            <td>
                <a href="{{ route('admin/breeds/breed/draft/edit', $breedDraft->id) }}">{{ $breedDraft->name }}</a>
                @if($breedDraft->isOfficial())
                <big><span class="label label-success">Real</span></big>
                @endif
            </td>
            <td>{{ is_null($breedDraft->submitted_at) ? '<em>Unknown</em>' : $breedDraft->submitted_at->format('F j, Y g:i A') }}</td>
        </tr>
        @endforeach()

        @if($breedDrafts->isEmpty())
        <tr>
            <td colspan="3">No breed draft to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $breedDrafts->appends(array_except(Input::all(), 'page'))->links() }}

@stop
