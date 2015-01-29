@extends($layout)



{{-- Page content --}}

@section('content')



<div class="row">

    <div class="col-md-6">

        <div class="panel panel-default">

            <div class="panel-heading clearfix">

                <i class="fa fa-arrow-left fa-2x"></i>

                <h3 class="panel-title">

                    <big>

                        Start Here

                    </big>

                </h3>

            </div>

            <div class="panel-body">

                <form class="form-horizontal" role="form" action="{{ route('auth/register') }}" method="post">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <input type="hidden" name="cc" value="{{ Input::get('cc') }}" />

                    <input type="hidden" name="referred_by_id" value="{{ Input::get('refid') }}" />

                    <div class="form-group">

                        <label for="registerBreed" class="col-sm-3 control-label">Breed</label>

                        <div class="col-sm-9">

                            <select name="register_breed" class="form-control" id="registerBreed" required>

                                @foreach($breeds as $breed)

                                	<option value="{{ $breed->id }}">{{ $breed->name }}</option>

                                @endforeach

                            </select>

                        </div>

                    </div>

                    <div class="form-group">

                        <label class="col-sm-3 control-label">Sex</label>

                        <div class="col-sm-9">

                            @foreach($sexes as $sex)

                                <label class="radio-inline" for="registerSex{{ $sex->name }}">

                                    <input type="radio" name="register_sex" id="registerSex{{ $sex->name }}" value="{{ $sex->id }}" {{ Input::old('register_sex', 1) == $sex->id ? 'checked' : '' }}/>

                                    {{ $sex->name }}

                                </label>

                            @endforeach

                        </div>

                    </div>

                    <div class="form-group">

                        <label for="registerDogName" class="col-sm-3 control-label">Name</label>

                        <div class="col-sm-9">

                            <input type="text" name="register_dog_name" class="form-control" id="registerDogName" placeholder="Name your new dog" maxlength="32" required />

                        </div>

                    </div>

                    <div class="form-group">

                        <div class="col-sm-12 text-right">

                            <button type="submit" name="register_begin" value="register_begin" class="btn btn-success btn-lg btn-block">Create Your First Dog</button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <div class="col-md-6">

        <h1 class="text-center">The most realistic breed creation game you’ve ever seen. <span class="text-primary">Registration is open and absolutely free!</span></h1>

    </div>

</div>

<div class="row">

    <div class="col-md-12">

        <p><big>Develop your own dog breeds by starting with modern breeds, and test them in contests against other players.</big></p>

    </div>

</div>

<div class="row">

    <div class="col-md-11 col-md-offset-1">

        <p>

            <big>

                Want to breed the best agility dog in existence?<br />

                What about a new line of curly-coated giant German Shepherds?<br />

                Or a breed that looks like a wolf but is the size of a Bulldog and makes a great family pet?

            </big>

        </p>

    </div>

</div>

<div class="row">

    <div class="col-md-12">

        <p><big>The game is a sandbox for your own goals. With over 150+ realistic genetically-controlled characteristics, each dog is truly an individual. Breed for temperament, uses in the field, a certain look, eradicate breed-specific diseases - or all of the above.</big></p>

        <p>

            <big>

                This is ultra realism, like nothing you’ve seen before.<br />

                To create your first dog, look above!

            </big>

        </p>

        <p>

            <img src="{{ asset('assets/img/homeimg.png') }}" class="img-responsive center-block" alt="Dog Banner" />

        </p>

    </div>

</div>



@stop

