<div id="characteristic-profiles-{{ $characteristic->id }}" class="characteristic-profile">
    <div class="form-group clearfix">
        <label class="col-sm-2 control-label text-right">Specify:</label>
        <div class="col-sm-10">
            @if($characteristic->isGenetic())
                @if( ! $phenotypes->isEmpty())
                <div id="phenotypes-{{ $characteristic->id }}">
                    <select name="ch[{{ $counter }}][ph][]" class="form-control input-xs" size="4" multiple>
                        @foreach($phenotypes as $phenotype)
                        <option value="{{ $phenotype->id }}" {{ (isset($searchedCharacteristic) and isset($searchedCharacteristic['ph']) and in_array($phenotype->id, $searchedCharacteristic['ph'])) ? 'selected' : '' }}>
                            {{ $phenotype->name }}
                        </option>
                        @endforeach
                    </select><br />
                </div>
                @endif

                @if( ! $characteristic->hideGenotypes() and ! $loci->isEmpty())
                    @if( ! $phenotypes->isEmpty())
                    <span class="center-block label label-primary">or</span>
                    <br />
                    @endif

                <div class="row" id="genotypes-{{ $characteristic->id }}">
                    @foreach($loci as $locus)
                    <div class="col-xs-4 text-center">
                        <strong>{{ $locus->name }}</strong>
                        <select name="ch[{{ $counter }}][g][{{ $locus->id }}]" class="form-control input-xs">
                            <option value=""></option>
                            @foreach($locus->genotypes as $genotype)
                            <option value="{{ $genotype->id }}" {{ (isset($searchedCharacteristic) and isset($searchedCharacteristic['g'][$locus->id]) and $genotype->id == $searchedCharacteristic['g'][$locus->id]) ? 'selected' : '' }}>
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
                        <div class="custom-female">
                            <a class="slider-bounds" data-toggle="tooltip" data-placement="top" title="{{ $characteristic->formatRangedValue($breedCharacteristic->min_female_ranged_value) }}">
                                <i class="fa fa-step-backward"></i>
                            </a>

                            <input name="ch[{{ $counter }}][r][f]" id="slider-{{ $counter }}-{{ $characteristic->id }}-female" type="text" value="{{ (isset($searchedCharacteristic) and isset($searchedCharacteristic['r']['f'])) ? $searchedCharacteristic['r']['f'] : $breedCharacteristic->min_female_ranged_value }}" data-slider-min="{{ round($breedCharacteristic->min_female_ranged_value) }}" data-slider-max="{{ round($breedCharacteristic->max_female_ranged_value) }}"/>

                            <a class="slider-bounds" data-toggle="tooltip" data-placement="top" title="{{ $characteristic->formatRangedValue($breedCharacteristic->max_female_ranged_value) }}">
                                <i class="fa fa-step-forward"></i>
                            </a>
                        </div>

                        <div class="custom-male">
                            <a class="slider-bounds" data-toggle="tooltip" data-placement="top" title="{{ $characteristic->formatRangedValue($breedCharacteristic->min_male_ranged_value) }}">
                                <i class="fa fa-step-backward"></i>
                            </a>

                            <input name="ch[{{ $counter }}][r][m]" id="slider-{{ $counter }}-{{ $characteristic->id }}-male" type="text" value="{{ (isset($searchedCharacteristic) and isset($searchedCharacteristic['r']['m'])) ? $searchedCharacteristic['r']['m'] : $breedCharacteristic->min_male_ranged_value }}" data-slider-min="{{ round($breedCharacteristic->min_male_ranged_value) }}" data-slider-max="{{ round($breedCharacteristic->max_male_ranged_value) }}"/>

                            <a class="slider-bounds" data-toggle="tooltip" data-placement="top" title="{{ $characteristic->formatRangedValue($breedCharacteristic->max_male_ranged_value) }}">
                                <i class="fa fa-step-forward"></i>
                            </a>
                        </div>
                   </div>
                    <script type="text/javascript">
                    $(document).ready(function(){
                        var female_range = $("#slider-{{ $counter }}-{{ $characteristic->id }}-female");
                        var male_range = $("#slider-{{ $counter }}-{{ $characteristic->id }}-male");

                        if ( ! female_range.parent().hasClass('slider')) {
                            female_range.slider({
                                step:1,
                                value:{{ (isset($searchedCharacteristic) and isset($searchedCharacteristic['r']['f'])) ? $searchedCharacteristic['r']['f'] : $breedCharacteristic->min_female_ranged_value }},
                                formater:function(value){
                                    {{ $characteristic->jsFormatRangedSlider() }}
                                }
                            });
                        }

                        if ( ! male_range.parent().hasClass('slider')) {
                            male_range.slider({
                                step:1,
                                value:{{ (isset($searchedCharacteristic) and isset($searchedCharacteristic['r']['m'])) ? $searchedCharacteristic['r']['m'] : $breedCharacteristic->min_male_ranged_value }},
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