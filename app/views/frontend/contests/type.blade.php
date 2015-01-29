@extends($layout)

{{-- Breadcrumbs --}}
{{ Breadcrumbs::setCurrentRoute('contests/type', $contestType) }}

{{-- Page content --}}
@section('content')

<div class="page-header">
    <div class="text-right pull-right">
        <a href="{{ route('contests/manage') }}" class="btn btn-primary">Manage Your Contests</a>
    </div>

    <h1>Editing Contest Type</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('contests/type/update', $contestType->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="form-group">
        <label for="contest-type-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="contest-type-name" value="{{{ Input::old('name', $contestType->name) }}}" required/>
        </div>
    </div>

    <div class="form-group">
        <label for="contest-type-description" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
            <input type="text" name="description" class="form-control" id="contest-type-description" value="{{{ Input::old('description', $contestType->description) }}}"/>
        </div>
    </div>

    <p class="text-right">
        <button type="submit" name="save_contest_type" class="btn btn-success">Save Contest Type</button>
        <a href="{{ route('contests/type/delete', $contestType->id) }}" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this contest type?');">Delete Contest Type</a>
    </p>
</form>

<h2>Prerequisites</h2>

<form class="form-horizontal" role="form" method="post" action="{{ route('contests/type/add_prerequisites', $contestType->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="form-group">
        <label for="contest-type-prerequisite" class="col-sm-2 control-label">Characteristic</label>
        <div class="col-sm-10">
            <select name="characteristics[]" class="form-control" id="contest-type-prerequisite" size="7" multiple>
                @foreach($prerequisiteCategories as $category)
                <optgroup label="{{ $category->parent->name }}: {{ $category->name }}">
                    @foreach($category->characteristics as $characteristic)
                    <option value="{{ $characteristic->id }}">{{ $characteristic->name }}</option>
                    @endforeach
                </optgroup>
                @endforeach

                @if( ! count($prerequisiteCategories))
                <option value="">No characteristics available</option>
                @endif
            </select>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="add_prerequisite" class="btn btn-primary">Add</button>
        </div>
    </div>
</form>

@foreach($prerequisites as $prerequisite)
<div class="well well-sm">
    <h3>{{ $prerequisite->characteristic->name }}</h3>

    <form class="form-horizontal" role="form" method="post" action="{{ route('contests/type/update_prerequisite', $contestType->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="prerequisite" value="{{ $prerequisite->id }}" />

        @if($prerequisite->characteristic->isGenetic())
        <hr />

            @if(count($allPhenotypes = $prerequisite->characteristic->queryPhenotypes()->orderBy('name', 'asc')->get()) > 0)
            <div class="form-group">
                <label for="contest-type-prerequisite-{{ $prerequisite->id }}-phenotypes" class="col-sm-2 control-label">
                    Phenotypes
                </label>
                <div class="col-sm-10">
                    <select name="phenotypes[]" class="form-control input-xs" size="2" multiple>
                        @foreach($allPhenotypes as $phenotype)
                        <option value="{{ $phenotype->id }}" {{ in_array($phenotype->id, $prerequisite->phenotypes()->lists('id')) ? 'selected' : '' }}>
                            {{ $phenotype->name }}
                        </option>
                        @endforeach
                    </select><br/>
                </div>
            </div>
            @endif

            @if(count($allLoci = $prerequisite->characteristic->loci()->whereActive()->orderBy('name', 'asc')->get()) > 0)
            <div class="form-group">
                <label for="contest-type-prerequisite-{{ $prerequisite->id }}-genotypes" class="col-sm-2 control-label">
                    Genotypes
                </label>
                <div class="col-sm-10">
                    <div class="row">
                        @foreach($allLoci as $locus)
                        <div class="col-xs-2 text-center">
                            <strong>{{ $locus->name }}</strong>
                            <select name="genotypes[]" class="form-control input-xs" size="2" multiple>
                                @foreach($locus->genotypes()->whereActive()->orderByAlleles()->get() as $genotype)
                                <option value="{{ $genotype->id }}" {{ in_array($genotype->id, $prerequisite->genotypes()->lists('id')) ? 'selected' : '' }}>
                                    {{ $genotype->toSymbol() }}
                                </option>
                                @endforeach
                            </select><br/>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        @endif

        @if($prerequisite->characteristic->isRanged())
        <div class="form-group">
            <label for="contest-type-prerequisite-{{ $prerequisite->id }}-range" class="col-sm-2 control-label">
                Range
            </label>
            <div class="col-sm-10">
                <p class="form-control-static">
                    <a class="slider-bounds" data-toggle="tooltip" data-placement="top" title="{{ $prerequisite->characteristic->ranged_lower_boundary_label }}">
                        <i class="fa fa-step-backward"></i>
                    </a> 

                    <input name="range" id="contest-type-prerequisite-{{ $prerequisite->id }}-range" type="text" value="{{ $prerequisite->min_ranged_value }},{{ $prerequisite->max_ranged_value }}" data-slider-min="{{ round($prerequisite->characteristic->min_ranged_value) }}" data-slider-max="{{ round($prerequisite->characteristic->max_ranged_value) }}"/>

                    <a class="slider-bounds" data-toggle="tooltip" data-placement="top" title="{{ $prerequisite->characteristic->ranged_upper_boundary_label }}">
                        <i class="fa fa-step-forward"></i>
                    </a>
                </p>

                <script type="text/javascript">
                $(document).ready(function(){
                    var range = $("#contest-type-prerequisite-{{ $prerequisite->id }}-range");

                    if ( ! range.parent().hasClass('slider')) {
                        range.slider({
                            step:1,
                            value:[{{ $prerequisite->min_ranged_value }},{{ $prerequisite->max_ranged_value }}],
                            formater:function(value){
                                {{ $prerequisite->characteristic->jsFormatRangedSlider() }}
                            }
                        });
                    }
                });
                </script>
            </div>
        </div>
        @endif

        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2 text-right">
                <button type="submit" name="save_prerequisite" class="btn btn-primary">Save</button>
                <a href="{{ route('contests/type/delete_prerequisite', $prerequisite->id) }}" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this prerequisite?');">Remove</a>
            </div>
        </div>
    </form>
</div>
@endforeach

<h2>Judging Requirements</h2>

<form class="form-horizontal" role="form" method="post" action="{{ route('contests/type/add_requirements', $contestType->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="form-group">
        <label for="contest-type-requirement" class="col-sm-2 control-label">Characteristic</label>
        <div class="col-sm-10">
            <select name="characteristics[]" class="form-control" id="contest-type-requirement" size="7" multiple>
                @foreach($requirementCategories as $category)
                <optgroup label="{{ $category->parent->name }}: {{ $category->name }}">
                    @foreach($category->characteristics as $characteristic)
                    <option value="{{ $characteristic->id }}">{{ $characteristic->name }}</option>
                    @endforeach
                </optgroup>
                @endforeach

                @if( ! count($requirementCategories))
                <option value="">No characteristics available</option>
                @endif
            </select>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="add_requirement" class="btn btn-primary">Add</button>
        </div>
    </div>
</form>

@foreach($requirements as $requirement)
<div class="well well-sm">
    <h3>{{ $requirement->characteristic->name }}</h3>

    <form class="form-horizontal" role="form" method="post" action="{{ route('contests/type/update_requirement', $contestType->id) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="judging_requirement" value="{{ $requirement->id }}" />

        <div class="form-group">
            <label for="contest-type-requirement-{{ $requirement->id }}-range" class="col-xs-2 control-label">
                Range
            </label>
            <div class="col-xs-10">
                <div class="row">
                    @foreach(UserContestTypeRequirement::getTypes() as $value => $name)
                    <label class="radio-inline">
                        <input type="radio" name="range" value="{{ $value }}" {{ $value == $requirement->type_id ? 'checked' : '' }} />
                        {{ $name }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-10 col-xs-offset-2 text-right">
                <button type="submit" name="save_requirement" class="btn btn-primary">Save</button>
                <a href="{{ route('contests/type/delete_requirement', $requirement->id) }}" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this judging requirement?');">Remove</a>
            </div>
        </div>
    </form>
</div>
@endforeach

@stop
