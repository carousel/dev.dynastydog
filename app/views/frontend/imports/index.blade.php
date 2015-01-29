@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Import Dogs</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Regular Imports
                </h3>
            </div>
            <div class="panel-body">
                <p class="text-center"><strong>You have {{ Dynasty::imports($currentUser->imports) }}</strong></p>
                
                <div class="callout callout-info">
                    <p>You get 2 free imports per day. They do not accumulate, and they refresh at midnight. You can purchase more in the cash shop. Purchased imports never expire. In-Game breeds will be importable so long as the population exceeds {{ Config::get('game.breed.active_extinction') }} {{ Str::plural('dog', Config::get('game.breed.active_extinction')) }}</p>
                </div>

                <form class="form" role="form" method="post" id="regular-import" action="{{ route('imports/import') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                    </div>
                    <div class="form-group">
                        <label for="import-name">Name:</label>
                        <input type="text" name="import_name" class="form-control" id="import-name" value="{{{ Input::old('import_name') }}}" required />
                        {{ $errors->first('import_name', '<span class="help-block">:message</span>') }}
                    </div>
                    <div class="form-group">
                        <label for="import-breed">Select Breed:</label>
                        <select name="import_breed" class="form-control" id="import-breed" required>
                            @foreach($breeds as $breed)
                            <option value="{{ $breed->id }}" {{ Input::old('import_breed', $breeds->first()->id) == $breed->id ? 'selected' : '' }}>
                                {{{ $breed->name }}}
                            </option>
                            @endforeach
                        </select>
                        {{ $errors->first('import_breed', '<span class="help-block">:message</span>') }}
                    </div>

                    <div class="form-horizontal" role="form">
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-left">Sex:</label>
                            <div class="col-sm-10">
                                @foreach($sexes as $sex)
                                <label class="radio-inline">
                                    <input type="radio" name="import_sex" id="import-custom-sex-{{ $sex->id }}" value="{{ $sex->id }}" {{ Input::old('import_sex', 1) == $sex->id ? 'checked' : '' }} > 
                                    {{ $sex->name }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label text-left">Age:</label>
                            <div class="col-sm-10">
                                @foreach($ages as $age => $label)
                                <p class="form-control-static">{{ $label }}</p>
                                @endforeach
                                {{ $errors->first('import_age', '<span class="help-block">:message</span>') }}
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="import_dog" data-loading-text="<i class='fa fa-cog fa-spin'></i> Importing..." class="btn btn-success btn-block btn-loading" onclick="return confirm('Are you sure you want to import this dog?');">Import Dog</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Custom Imports
                </h3>
            </div>
            <div class="panel-body">
                <p class="text-center"><strong>You have {{ Dynasty::customImports($currentUser->custom_imports) }}</strong></p>

                <div class="callout callout-info">
                    <p>Custom imports allow you to specify up to 3 characteristics of an import, provided they fit into the breed standard of the selected breed. It is recommended you play with the form before purchasing a custom import in the cash shop.</p>
                </div>

                <form class="form" role="form" method="post" id="custom-import" action="{{ route('imports/custom_import') }}">
                    <fieldset id="custom-imports" disabled>
                        <h5 class="text-center text-muted spinner">
                            <em>
                                <i class="fa fa-spinner fa-spin"></i>
                                Loading...
                            </em>
                        </h5>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group">
                        </div>
                        <div class="form-group">
                            <label for="import-custom-name">Name:</label>
                            <input type="text" name="custom_import_name" class="form-control" id="import-custom-name" required />
                        </div>
	                    <div class="form-group">
	                        <label for="import-breed">Select Breed:</label>
	                        <select name="custom_import_breed" class="form-control" id="import-custom-breed" required>
	                            @foreach($breeds as $breed)
	                            <option value="{{ $breed->id }}" {{ Input::old('custom_import_breed', $breeds->first()->id) == $breed->id ? 'selected' : '' }}>
	                                {{{ $breed->name }}}
	                            </option>
	                            @endforeach
	                        </select>
	                    </div>

                        <div class="form-horizontal" role="form">
                            <div class="form-group">
                                <label class="col-sm-2 control-label text-left">Sex:</label>
                                <div class="col-sm-10">
                                    @foreach($sexes as $sex)
                                    <label class="radio-inline">
                                        <input type="radio" name="custom_import_sex" id="import-custom-sex-{{ $sex->id }}" value="{{ $sex->id }}" {{ Input::old('custom_import_sex', 1) == $sex->id ? 'checked' : '' }} > 
                                        {{ $sex->name }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label text-left">Age:</label>
                                <div class="col-sm-10">
                                    @foreach($ages as $age => $label)
                                    <p class="form-control-static">{{ $label }}</p>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-link" id="import-custom-add-characteristic">
                            <i class="fa fa-plus-circle"></i> Add Characteristic
                        </button>

                        <button type="submit" name="import_custom_dog" data-loading-text="<i class='fa fa-cog fa-spin'></i> Importing..." class="btn btn-success btn-block btn-loading" onclick="return confirm('Are you sure you want to import this dog?');">Import Custom Dog</button>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="import-custom-errors" tabindex="-1" role="dialog" aria-labelledby="import-custom-errors-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="import-custom-errors-label">Oops!</h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@stop

{{-- JS assets --}}
@section('js_assets')
@parent
<script type="text/javascript" src="{{ asset('assets/js/import.js') }}"></script>
<script type="text/javascript">
$(function() {
    dogGame.custom_import.init({
        counter: {{ $counter }}
    });
});
</script>
@stop
