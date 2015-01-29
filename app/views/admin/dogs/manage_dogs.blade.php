@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Manage Dogs</h1>
</div>
<h2>Find</h2>

<form class="form" role="form" method="post" action="{{ route('admin/dogs/dog/find') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="row">
        <div class="col-xs-9">
            <div class="input-group">
                <span class="input-group-addon">#</span>
                <input type="number" min="1" name="dog" class="form-control" id="cp-dogs-dog-manage-goto" placeholder="Dog ID" required />
            </div>
        </div>
        <div class="col-xs-3">
            <button type="submit" name="go_to_dog" class="btn btn-primary btn-block">Go To</button>
        </div>
    </div>
</form>

<br /><br />

<h2>Age</h2>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/dogs/manage/age_dogs') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <div class="col-xs-12">
            <input type="number" name="months" class="form-control" id="cp-dogs-age-months" value="{{{ Input::old('months') }}}" min="1" max="99" placeholder="# Months" required>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-6">
            <button type="submit" name="age_dogs" value="increase" class="btn btn-success btn-block" onclick="return confirm('Are you sure you want to increase the age of all dogs?');">
                <i class="fa fa-fw fa-arrow-up"></i> Increase Age
            </button>
        </div>
        <div class="col-sm-6">
            <button type="submit" name="age_dogs" value="decrease" class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to decrease the age of all dogs?');">
                <i class="fa fa-fw fa-arrow-down"></i> Decrease Age
            </button>
        </div>
    </div>
</form>

@stop
