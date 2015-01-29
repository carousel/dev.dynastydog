@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Existing Locus Alleles</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Search Options</big>
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-locus-alleles">
            <div class="form-group">
                <label for="search-locus-alleles-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <input type="text" name="id" class="form-control" id="search-locus-alleles-id" value="{{{ Input::get('id') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-locus-alleles-symbol" class="col-sm-2 control-label">Symbol</label>
                <div class="col-sm-10">
                    <input type="text" name="symbol" class="form-control" id="search-locus-alleles-symbol" value="{{{ Input::get('symbol') }}}" />
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="loci_alleles" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{ $locusAlleles->appends(array_except(Input::all(), 'page'))->links() }}

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Symbol</th>
            <th>Locus</th>
        </tr>
    </thead>
    <tbody>
        @foreach($locusAlleles as $locusAllele)
        <tr>
            <td><a href="{{ route('admin/genetics/locus/allele/edit', $locusAllele->id) }}">{{ $locusAllele->id }}</a></td>
            <td><a href="{{ route('admin/genetics/locus/allele/edit', $locusAllele->id) }}">{{ $locusAllele->symbol }}</a></td>
            <td><a href="{{ route('admin/genetics/locus/edit', $locusAllele->locus->id) }}">{{ $locusAllele->locus->name }}</a></td>
        </tr>
        @endforeach()

        @if($locusAlleles->isEmpty())
        <tr>
            <td colspan="3">No locus alleles to display.</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $locusAlleles->appends(array_except(Input::all(), 'page'))->links() }}

@stop
