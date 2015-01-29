@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Genotype</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/genetics/genotype/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-genotype-locus_allele_a" class="col-sm-4 control-label">Allele A</label>
                <div class="col-sm-8">
                        <select name="locus_allele_a" class="form-control" id="cp-genotype-locus_allele_a" required>
                            <optgroup label="All">
                                <option value="" {{ ( ! Input::old('locus_allele_a')) ? 'selected' : '' }}>{{ LocusAllele::NULL_SYMBOL }}</option>
                            </optgroup>

                            @foreach($loci as $locus)
                            <optgroup label="{{ $locus->name }}">
                                @foreach($locus->alleles as $locusAllele)
                                <option value="{{ $locusAllele->id }}" {{ ($locusAllele->id == Input::old('locus_allele_a')) ? 'selected' : '' }}>{{ $locusAllele->symbol }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach

                            @if($loci->isEmpty())
                            <option value="">No genotypes available</option>
                            @endif
                        </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-genotype-locus_allele_b" class="col-sm-4 control-label">Allele B</label>
                <div class="col-sm-8">
                        <select name="locus_allele_b" class="form-control" id="cp-genotype-locus_allele_b" required>
                            <optgroup label="All">
                                <option value="" {{ ( ! Input::old('locus_allele_b')) ? 'selected' : '' }}>{{ LocusAllele::NULL_SYMBOL }}</option>
                            </optgroup>

                            @foreach($loci as $locus)
                            <optgroup label="{{ $locus->name }}">
                                @foreach($locus->alleles as $locusAllele)
                                <option value="{{ $locusAllele->id }}" {{ ($locusAllele->id == Input::old('locus_allele_b')) ? 'selected' : '' }}>{{ $locusAllele->symbol }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach

                            @if($loci->isEmpty())
                            <option value="">No genotypes available</option>
                            @endif
                        </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-genotype-available_to_female" class="col-sm-5 control-label">Available to Females?</label>
                <div class="col-sm-7">
                    <div class="checkbox">
                        <label for="cp-genotype-available_to_female">
                            <input type="checkbox" name="available_to_female" value="yes" id="cp-genotype-available_to_female" {{ (Input::old('available_to_male') == 'yes') ? 'checked' : '' }}/> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="cp-genotype-available_to_male" class="col-sm-5 control-label">Available to Males?</label>
                <div class="col-sm-7">
                    <div class="checkbox">
                        <label for="cp-genotype-available_to_male">
                            <input type="checkbox" name="available_to_male" value="yes" id="cp-genotype-available_to_male" {{ (Input::old('available_to_male') == 'yes') ? 'checked' : '' }}/> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_genotype" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop
