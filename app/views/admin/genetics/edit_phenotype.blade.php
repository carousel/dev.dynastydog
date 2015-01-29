@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Phenotype</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/genetics/phenotype/edit', $phenotype->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-phenotype-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $phenotype->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-phenotype-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-phenotype-name" value="{{{ Input::old('name', $phenotype->name) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-phenotype-priority" class="col-sm-2 control-label">Priority</label>
        <div class="col-sm-10">
            <input type="number" name="priority" min="0" max="255" placeholder="0" class="form-control" id="cp-phenotype-priority" value="{{{ Input::old('priority', $phenotype->priority) }}}" />
        </div>
    </div>

    <div class="form-group">
        <label for="cp-phenotype-genotypes" class="col-sm-2 control-label">Genotypes</label>
        <div class="col-sm-10">
            <div class="row">
                @foreach($loci as $locus)
                <div class="col-sm-4">
                    <h5><strong>{{ $locus->name }}</strong></h5>
                    @foreach($locus->genotypes as $genotype)
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="genotypes[]" value="{{ $genotype->id }}" {{ in_array($genotype->id, (array)Input::old('genotypes', $phenotype->genotypes()->lists('id'))) ? 'checked' : '' }} />
                            {{ $genotype->toSymbol() }}
                        </label>
                    </div>
                    @endforeach
                    <hr />
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/genetics/phenotype/delete', $phenotype->id) }}" name="delete_phenotype" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this phenotype?');">Delete</a>
            <a href="{{ route('admin/genetics/phenotype/clone', $phenotype->id) }}" name="clone_phenotype" class="btn btn-default" onclick="return confirm('Are you sure you want to clone this phenotype?');">
                <i class="fa fa-fw fa-code-fork"></i> Clone
            </a>
            <button type="submit" name="edit_phenotype" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@stop
