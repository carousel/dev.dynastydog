@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Breed Registry - Submit In-Game Breed</h1>
</div>

<p>You can submit a new breed at any time, provided you have a 5-generation record of dogs that have been tested and adhere to the breed standard of the new breed you wish to create. ALL dogs that are 5 generations back from your submitted dog must adhere to the breed standard. The dog you are submitting counts as 1st Generation.</p>

<p>
    Rules:
    <ol>
        <li>The proposed breed must have a population of at least 50 living individuals that fit the breed standard of this breed.</li>
        <li>Your breed's name must be appropriate and inoffensive, as well as sounding similar to what a dog breed might sound like. </li>
        <li>The breed's description cannot contain anything offensive or that otherwise goes against the game's rules.</li>
        <li>If you are submitting an image with your entry, you must have the rights to use it. Art and photo theft will not be tolerated.</li>
        <li>You must own the dog that you are submitting as the Originator of the breed. </li>
        <li>Breeds whose active (meaning: aged this week) populations drop below 20 individuals will become extinct and their breed entry removed. Extinction happens every Sunday. Individual dogs are not touched.</li>
        <li>You would have 7 days after the acceptance of your breed to register the minimum 20 dogs as your new breed, before the breed becomes eligible for extinction.</li>
    </ol>
</p>

<p>Your submitted breed will need to be approved by the mod/admin team before dogs can be registered as that breed. You will be able to add/change/remove the breed image and the common health disorders both before and after acceptance of the breed. Failure to comply with the above rules could lead to the rejection of your breed entry. The mod/admin team holds the final say as to whether a breed is accepted or rejected and can reject a breed for any reason including those not listed above.</p>

<form role="form" method="post" action="{{ route('breed_registry/drafts/new') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <p class="text-center">
        <button type="submit" name="create_draft" class="btn btn-success" data-loading-text="<i class='fa fa-cog fa-spin'></i> Creating...">
            I accept the rules above. Create new in-game breed draft.
        </button>
    </p>
</form>

@stop

{{-- JS assets --}}
@section('js_assets')
@parent
<script type="text/javascript" src="{{ asset('assets/js/breed_registry.js') }}"></script>
@stop
