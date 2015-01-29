@if(is_null($selectedCharacteristic))
<div id="characteristics-select">
@endif
	<button type="button" class="btn btn-link characteristics-remove-characteristic">
	    <span class="text-danger"><i class="fa fa-times-circle"></i></span>
	</button>

	<label>Select Characteristic:</label>

	<select name="ch[{{ $counter }}][id]" class="form-control" id="characteristics-select-{{ $counter }}">
	    <option value=""></option>
	    @foreach($characteristicCategories as $category)
	    <optgroup label="{{ $category['parent_name'] }}: {{ $category['name'] }}">
	        @foreach($category['characteristics'] as $characteristic)
	        <option value="{{ $characteristic->id }}" {{ ( ! is_null($selectedCharacteristic) and $selectedCharacteristic->id == $characteristic->id) ? 'selected' : '' }}>{{ $characteristic->name }}</option>
	        @endforeach
	    </optgroup>
	    @endforeach
	</select>
	<br />
@if(is_null($selectedCharacteristic))
</div>
@endif