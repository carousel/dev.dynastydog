@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Locus Allele</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/genetics/locus/allele/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-locusallele-symbol" class="col-sm-2 control-label">Symbol</label>
        <div class="col-sm-10">
            <input type="text" name="symbol" class="form-control" id="cp-locusallele-symbol" value="{{{ Input::old('symbol') }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-locusallele-locus" class="col-sm-2 control-label">Locus</label>
        <div class="col-sm-10">
            <select id="cp-locusallele-locus" name="locus" class="form-control">
                @foreach($loci as $locus)
                    <option value="{{ $locus->id }}" {{ (Input::old('locus') == $locus->id) ? 'selected' : '' }}>{{ $locus->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_locus_allele" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop
