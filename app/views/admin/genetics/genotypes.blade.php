@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Genotypes</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-genotypes">
            <div class="form-group">
                <label for="search-genotypes-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-genotypes-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-genotypes-sequence" class="col-sm-2 control-label">Sequence</label>
                <div class="col-sm-10">
                    <input type="text" name="sequence" class="form-control" id="search-genotypes-sequence" value="{{{ Input::get('sequence') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="genotypes" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $genotypes->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Sequence</th>
            <th>Loci</th>
            <th>Available To</th>
        </tr>
    </thead>
    <tbody>
        @foreach($genotypes as $genotype)
        <tr>
            <td><a href="{{ route('admin/genetics/genotype/edit', $genotype->id) }}">{{ $genotype->id }}</a></td>
            <td><a href="{{ route('admin/genetics/genotype/edit', $genotype->id) }}">{{ $genotype->toSymbol() }}</a></td>
            <td><a href="{{ route('admin/genetics/locus/edit', $genotype->locus->id) }}">{{ $genotype->locus->name }}</a></td>
            <td>
                @if($genotype->isAvailableToFemales())
                <big><span class="label label-danger">Female</span></big>
                @endif

                @if($genotype->isAvailableToMales())
                <big><span class="label label-info">Male</span></big>
                @endif

                @if( ! $genotype->isAvailableToFemales() and ! $genotype->isAvailableToMales())
                <big><span class="label label-default">None</span></big>
                @endif
            </td>
        </tr>
        @endforeach()

        @if($genotypes->isEmpty())
        <tr>
            <td colspan="4">No genotypes to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $genotypes->appends(array_except(Input::all(), 'page'))->links() }}

@stop
