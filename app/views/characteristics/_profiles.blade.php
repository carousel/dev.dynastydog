<div id="characteristic-profiles-{{ $characteristic->id }}" class="characteristic-profile">
    <div class="form-group clearfix">
        <label class="col-sm-2 control-label text-right">Specify:</label>
        <div class="col-sm-10">
            @if($characteristic->isGenetic())
                @if(($phenotypes = $characteristic->queryPhenotypes()->orderBy('name', 'asc')->get()) and ! $phenotypes->isEmpty())
                <div id="phenotypes-{{ $characteristic->id }}">
                    <select name="ch[{{ $counter }}][ph][]" class="form-control input-xs" size="2" multiple>
                        @foreach($phenotypes as $phenotype)
                        <option value="{{ $phenotype->id }}" {{ (isset($searchedCharacteristic) and isset($searchedCharacteristic['ph']) and in_array($phenotype->id, $searchedCharacteristic['ph'])) ? 'selected' : '' }}>
                            {{ $phenotype->name }}
                        </option>
                        @endforeach
                    </select><br />
                </div>
                @endif

                @if( ! $characteristic->hideGenotypes() and count($loci = $characteristic->loci()->with(array('genotypes' => function($query){ $query->orderByAlleles(); }))->whereActive()->orderBy('name', 'asc')->get()) > 0)
                <div class="row" id="genotypes-{{ $characteristic->id }}">
                    @foreach($loci as $locus)
                    <div class="col-xs-4 text-center">
                        <strong>{{ $locus->name }}</strong>
                        <select name="ch[{{ $counter }}][g][{{ $locus->id }}][]" class="form-control input-xs" size="2" multiple>
                            @foreach($locus->genotypes as $genotype)
                            <option value="{{ $genotype->id }}" {{ (isset($searchedCharacteristic) and isset($searchedCharacteristic['g']) and in_array($genotype->id, array_flatten((array) $searchedCharacteristic['g']))) ? 'selected' : '' }}>
                                {{ $genotype->toSymbol() }}
                            </option>
                            @endforeach
                        </select><br />
                    </div>
                    @endforeach
                </div>
                @endif
            @endif

            @if($characteristic->isRanged())
            <div id="characteristics-{{ $counter }}-range-{{ $characteristic->id }}">
                <p class="form-control-static">
                    
                    <div class="slider-{{ $counter }}">
                        <a class="slider-bounds" data-toggle="tooltip" data-placement="top" title="{{ $characteristic->ranged_lower_boundary_label }}">
                            <i class="fa fa-step-backward"></i>
                        </a> 

                        <input name="ch[{{ $counter }}][r]" id="slider-{{ $counter }}-{{ $characteristic->id }}" type="text" value="{{ (isset($searchedCharacteristic) and isset($searchedCharacteristic['r'])) ? $searchedCharacteristic['r'] : $characteristic->min_ranged_value.','.$characteristic->max_ranged_value }}" data-slider-min="{{ round($characteristic->min_ranged_value) }}" data-slider-max="{{ round($characteristic->max_ranged_value) }}"/>

                        <a class="slider-bounds" data-toggle="tooltip" data-placement="top" title="{{ $characteristic->ranged_upper_boundary_label }}">
                            <i class="fa fa-step-forward"></i>
                        </a>
                   </div>
                    <script type="text/javascript">
                    $(document).ready(function(){
                        var range = $("#slider-{{ $counter }}-{{ $characteristic->id }}");

                        if ( ! range.parent().hasClass('slider')) {
                            range.slider({
                                step:1,
                                value:[{{ (isset($searchedCharacteristic) and isset($searchedCharacteristic['r'])) ? $searchedCharacteristic['r'] : $characteristic->min_ranged_value.','.$characteristic->max_ranged_value }}],
                                formater:function(value){
                                    {{ $characteristic->jsFormatRangedSlider() }}
                                }
                            });
                        }
                    });
                    </script>
                </p>
            </div>
            @endif
        </div>
    </div>
</div>