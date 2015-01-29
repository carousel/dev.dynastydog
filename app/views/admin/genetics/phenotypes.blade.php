@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Phenotypes</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-phenotypes">
            <div class="form-group">
                <label for="search-phenotypes-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-phenotypes-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-phenotypes-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-phenotypes-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="phenotypes" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $phenotypes->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Genotypes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($phenotypes as $phenotype)
        <tr>
            <td><a href="{{ route('admin/genetics/phenotype/edit', $phenotype->id) }}">{{ $phenotype->id }}</a></td>
            <td><a href="{{ route('admin/genetics/phenotype/edit', $phenotype->id) }}">{{ $phenotype->name }}</a></td>
            <td>
                <ul>
                    @foreach(($loci = $phenotype->loci()->get()) as $locus)
                    <li>
                        <a href="{{ route('admin/genetics/locus/edit', $locus->id) }}">{{ $locus->name }}</a>: 
                        @foreach($locus->genotypes as $genotype)
                        <a href="{{ route('admin/genetics/genotype/edit', $genotype->id) }}">{{ $genotype->toSymbol() }}</a>
                        @endforeach
                    </li>
                    @endforeach

                    @if($loci->isEmpty())
                    <li><em>None</em></li>
                    @endif
                </ul>
            </td>
        </tr>
        @endforeach()

        @if($phenotypes->isEmpty())
        <tr>
            <td colspan="4">No phenotypes to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $phenotypes->appends(array_except(Input::all(), 'page'))->links() }}

@stop
