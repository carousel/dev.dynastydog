@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <div class="text-right pull-right">
        <div class="btn-group">
            <a href="{{ route('breed_registry/drafts/official') }}" class="btn btn-default">
                View List of In-Progress Real Breeds
            </a>
        </div>
    </div>

    <h1>Breed Registry - Submit Real Breed</h1>
</div>

<p>Is your favourite real breed missing from Dynasty? You can help us get it added faster! Fill out this form to the best of your ability - don’t worry, you can save your progress - and submit it for our admins to look over and finish up the behind-the-scenes portions.</p>

<p>Make sure you fill out every single characteristic! The more realistic and true to the breed you make this submission, the faster it will get approved and added to the game. It is not necessary to fill out both phenotypes and genotypes, although you can if you want to. Just one or the other will suffice. <kbd>Ctrl+click</kbd> (<kbd>cmd+click</kbd> for mac) will allow you to select multiple options, or deselect all options in any box.</p>

<p>This is for real breeds only! If you submit an in-game breed, it will get rejected.</p>

<form role="form" method="post" action="{{ route('breed_registry/drafts/official/new') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <p class="text-center">
        <button type="submit" name="create_draft" class="btn btn-success" data-loading-text="<i class='fa fa-cog fa-spin'></i> Creating...">I accept the rules above. Create new real breed draft.</button>
    </p>
</form>

@stop

{{-- JS assets --}}
@section('js_assets')
@parent
<script type="text/javascript" src="{{ asset('assets/js/breed_registry.js') }}"></script>
@stop
