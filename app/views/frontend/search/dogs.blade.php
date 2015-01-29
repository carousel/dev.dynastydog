@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Search Dogs</h1>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h3 class="panel-title">
            <big>Options</big>
        </h3>
    </div>

    <div class="panel-body">
        <form class="form-horizontal" role="form" method="get" id="search-dogs">
            <div class="form-group">
                <label for="search-dogs-id" class="col-sm-2 control-label">ID</label>
                <div class="col-sm-10">
                    <div class="input-group">
                        <span class="input-group-addon">#</span>
                        <input type="text" name="id" class="form-control" id="search-dogs-id" value="{{{ Input::get('id') }}}" placeholder="Dog ID" />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="search-dogs-name" class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" id="search-dogs-name" value="{{{ Input::get('name') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-dogs-sex" class="col-sm-2 control-label">Sex</label>
                <div class="col-sm-10">
                    @foreach($sexes as $sex)
                    <label class="radio-inline">
                        <input type="radio" name="sex" id="search-dogs-sex-{{ $sex->id }}" value="{{ $sex->id }}" {{ (Input::get('sex') == $sex->id) ? 'checked' : '' }}> 
                        {{ $sex->name }}
                    </label>
                    @endforeach
                    <label class="radio-inline">
                        <input type="radio" name="sex" id="search-dogs-sex-any" value="" {{ (Input::get('sex', '') == '') ? 'checked' : '' }}> 
                        Any
                    </label>
                   </div>
            </div>

            <div class="form-group">
                <label for="search-dogs-age" class="col-sm-2 control-label">Age</label>
                <div class="col-sm-5">
                    <input type="text" name="minimum_age" class="form-control" id="search-dogs-age-min" value="{{{ Input::get('minimum_age') }}}" placeholder="Minimum" />
                </div>
                <div class="col-sm-5">
                    <input type="text" name="maximum_age" class="form-control" id="search-dogs-age-max" value="{{{ Input::get('maximum_age') }}}" placeholder="Maximum" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-dogs-breed" class="col-sm-2 control-label">Breed</label>
                <div class="col-sm-10">
                    <select name="breed" class="form-control" id="search-dogs-breed">
                        <option value="">Any</option>
                        <option value="unregistered">Unregistered</option>
                        @foreach($breeds as $breed)
                        <option value="{{ $breed->id }}" {{ (Input::get('breed') == $breed->id) ? 'selected' : '' }}>{{{ $breed->name }}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="search-dogs-owner" class="col-sm-2 control-label">Owner ID</label>
                <div class="col-sm-10">
                    <div class="input-group">
                        <span class="input-group-addon">#</span>
                        <input type="text" name="owner" class="form-control" id="search-dogs-owner" value="{{{ Input::get('owner') }}}" placeholder="Owner ID" />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="search-dogs-breeder" class="col-sm-2 control-label">Breeder ID</label>
                <div class="col-sm-10">
                    <div class="input-group">
                        <span class="input-group-addon">#</span>
                        <input type="text" name="breeder" class="form-control" id="search-dogs-breeder" value="{{{ Input::get('breeder') }}}" placeholder="Breeder ID" />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="search-dogs-prefix" class="col-sm-2 control-label">Kennel Prefix</label>
                <div class="col-sm-10">
                    <input type="text" name="kennel_prefix" class="form-control" id="search-dogs-prefix" value="{{{ Input::get('kennel_prefix') }}}" />
                </div>
            </div>

            <div class="form-group">
                <label for="search-dogs-studding" class="col-sm-2 control-label">Up for Stud</label>
                <div class="col-sm-10">
                    @foreach($studdingOptions as $id => $option)
                    <label class="checkbox-inline">
                        <input type="checkbox" name="studding_options[]" id="search-dogs-studding-{{ $id }}" value="{{ $id }}" {{ in_array($id, Input::get('studding_options', [])) ? 'checked' : '' }} /> 
                        {{ $option }}
                    </label>
                    @endforeach
                   </div>
            </div>

            <div class="form-group">
                <label for="search-dogs-status" class="col-sm-2 control-label">Status</label>
                <div class="col-sm-10">
                    <label class="radio-inline">
                        <input type="radio" name="status" id="search-dogs-status-active" value="active" {{ (Input::get('status') == 'active') ? 'checked' : '' }}> 
                        Active
                    </label>

                    <label class="radio-inline">
                        <input type="radio" name="status" id="search-dogs-status-alive" value="alive" {{ (Input::get('status') == 'alive') ? 'checked' : '' }}> 
                        Alive
                    </label>

                    <label class="radio-inline">
                        <input type="radio" name="status" id="search-dogs-status-all" value="all" {{ (Input::get('status', 'all') == 'all') ? 'checked' : '' }}> 
                        All
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="healthy" id="search-dogs-healthy" value="yes" {{ (Input::get('healthy') == 'yes') ? 'checked' : '' }}> 
                        Dog must have no current health issues
                    </label>
                </div>
            </div>

            @foreach($searchedCharacteristics as $index => $searchedCharacteristic)
            <div class="characteristic-wrapper clearfix">
                @include('characteristics/_dropdown', array(
                    'characteristicCategories' => $characteristicCategories, 
                    'selectedCharacteristic' => $searchedCharacteristic['characteristic'], 
                    'counter' => $counter, 
                ))

                @include('characteristics/_profiles', array(
                    'characteristic' => $searchedCharacteristic['characteristic'], 
                    'searchedCharacteristic' => $searchedCharacteristic, 
                    'counter' => $counter++, 
                ))
            </div>
            @endforeach

            @include('characteristics/_add')

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2 text-right">
                    <button type="submit" name="search" value="dogs" data-loading-text="<i class='fa fa-cog fa-spin'></i> Searching..." class="btn btn-primary btn-loading">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if( ! is_null($results))
<table class="table table-striped">
    <thead>
        <tr>
            <th class="col-xs-1 text-right">ID</th>
            <th>Dog</th>
            @if($showCharacteristics)
            <th class="col-xs-8 text-center">Characteristics</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($results as $dog)
        <tr>
            <td class="text-right">{{ $dog->id }}</td>
            <td>{{ $dog->linkedNameplate() }}</td>
            @if($showCharacteristics)
            <td>
                <ul class="list-unstyled">
                    @foreach($dog->characteristics as $dogCharacteristic)
                    <div class="row">
                        <div class="col-xs-4 text-right">
                            <strong>{{ $dogCharacteristic->characteristic->name }}:</strong>
                        </div>
                        <div class="col-xs-8">
                            @if($dogCharacteristic->rangedValueIsRevealed())
                            <div class="progress-group" id="dog-characteristic-range-{{ $dogCharacteristic->id }}">
                                <span class="progress-group-addon">
                                    <a class="range-bounds" data-toggle="tooltip" data-placement="top" title="{{ $dogCharacteristic->characteristic->ranged_lower_boundary_label }}">
                                        <i class="fa fa-step-backward"></i>
                                    </a>
                                </span>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" aria-valuetransitiongoal="{{ $dogCharacteristic->current_ranged_value}}" aria-valuemin="{{ $dogCharacteristic->characteristic->min_ranged_value }}" aria-valuemax="{{ $dogCharacteristic->characteristic->max_ranged_value }}" data-label="{{ $dogCharacteristic->formatRangedValue() }}"></div>
                                </div>
                                <span class="progress-group-addon">
                                    <a class="range-bounds" data-toggle="tooltip" data-placement="top" title="{{ $dogCharacteristic->characteristic->ranged_upper_boundary_label }}">
                                        <i class="fa fa-step-forward"></i>
                                    </a>
                                </span>
                            </div>

                            <script type="text/javascript">
                            $(function()
                            {
                                $("#dog-characteristic-range-{{ $dogCharacteristic->id }} .progress .progress-bar").each(function(){
                                    var progress=$(this);
                                    var label=progress.attr("data-label");
                                    progress.progressbar({
                                        display_text:"center",
                                        use_percentage:false,
                                        amount_format:function(e,i){
                                            return label;
                                        }
                                    });
                                });
                            });
                            </script>
                            @endif

                            @if($dogCharacteristic->phenotypesAreRevealed())
                            @foreach($dogCharacteristic->phenotypes as $phenotype)
                            {{ $phenotype->name }}
                            @endforeach
                            @endif

                            @if($dogCharacteristic->phenotypesAreRevealed() and $dogCharacteristic->genotypesAreRevealed())
                            //
                            @endif

                            @if( ! $dogCharacteristic->characteristic->hideGenotypes() and $dogCharacteristic->genotypesAreRevealed())
                            @foreach($dogCharacteristic->genotypes as $genotype)
                            {{ $genotype->toSymbol() }}
                            @endforeach
                            @endif
                        </div>
                    </div>
                    @endforeach
                </ul>
            </td>
            @endif
        </tr>
        @endforeach

        @if($results->isEmpty())
        <tr>
            <td colspan="{{ $showCharacteristics ? 3 : 2 }}">No results found</td>
        </tr>
        @endif
    </tbody>
</table>

{{ $results->appends(array_except(Input::all(), 'page'))->links() }}
@endif

@stop

{{-- JS assets --}}
@section('js_assets')
@parent
<script type="text/javascript" src="{{ asset('assets/js/dog_search.js') }}"></script>
<script type="text/javascript">
$(function() {
    dogGame.characteristic_search.init({
        counter: {{ $counter }}
    });
});
</script>
@stop
