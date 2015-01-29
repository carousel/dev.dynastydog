@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <div class="text-right pull-right">
        <div class="btn-group">
            <a href="{{ route('breed_registry/drafts/new') }}" class="btn btn-success">Submit In-Game Breed</a>
            <a href="{{ route('breed_registry/drafts/official/new') }}" class="btn btn-success">Submit Real Breed</a>
        </div>
    </div>

    <h1>Manage Your Breeds</h1>
</div>

<div class="text-right">
    <div class="btn-group">
        <a href="{{ route('breed_registry/drafts/official') }}" class="btn btn-default btn-sm">View List of In-Progress Real Breeds</a>
    </div>
</div>

<h2>Drafts</h2>

<div class="row">
    @foreach($breedDrafts as $breedDraft)
    <div class="go-to-breed col-xs-6 col-md-3">
        <div class="panel panel-default">
            @if($breedDraft->hasImage())
            <div class="panel-body">
                <a href="{{ route('breed_registry/draft/form', $breedDraft->id) }}">
                    <img src="{{ asset($breedDraft->getImageUrl()) }}" alt="Breed Image" title="Breed Image" class="img-responsive center-block" />
                </a>
            </div>
            @endif

            <div class="panel-footer panel-nav">
                <ul class="nav nav-pills bordered nav-justified">
                    <li>
                        <a href="{{ route('breed_registry/draft/form', $breedDraft->id) }}">
                            {{{ $breedDraft->name }}} <i class='loading fa fa-cog fa-spin hidden'></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endforeach

    @if($breedDrafts->isEmpty())
    <div class="col-md-12">
        <p>You do not have any breed drafts.</p>
    </div>
    @endif
</div>

<h2>Pending</h2>

<div class="row">
    @foreach($pendingDrafts as $breedDraft)
    <div class="col-xs-6 col-md-3">
        <div class="panel panel-default">
            @if($breedDraft->hasImage())
            <div class="panel-body">
                <a href="{{ route('breed_registry/draft/submitted', $breedDraft->id) }}">
                    <img src="{{ asset($breedDraft->getImageUrl()) }}" alt="Breed Image" title="Breed Image" class="img-responsive center-block" />
                </a>
            </div>
            @endif

            <div class="panel-footer panel-nav">
                <ul class="nav nav-pills bordered nav-justified">
                    <li>
                        <a href="{{ route('breed_registry/draft/submitted', $breedDraft->id) }}">
                            {{{ $breedDraft->name }}}
                        </a>
                    </li>
                </ul>

                <a class="btn btn-block btn-primary btn-xs" href="{{ route('breed_registry/draft/submitted/revert', $breedDraft->id) }}" onclick="return confirm('Are you sure you want to edit this breed submission? You will have to resubmit it again once you\'re done editing.');">
                    Revert to Draft
                </a>
            </div>
        </div>
    </div>
    @endforeach

    @if($pendingDrafts->isEmpty())
    <div class="col-md-12">
        <p>You do not have any pending breeds.</p>
    </div>
    @endif
</div>

<h2>Rejected</h2>

<div class="row">
    @foreach($rejectedDrafts as $breedDraft)
    <div class="col-xs-6 col-md-3">
        <div class="panel panel-default">
            @if($breedDraft->hasImage())
            <div class="panel-body">
                <a href="{{ route('breed_registry/draft/submitted', $breedDraft->id) }}">
                    <img src="{{ asset($breedDraft->getImageUrl()) }}" alt="Breed Image" title="Breed Image" class="img-responsive center-block" />
                </a>
            </div>
            @endif

            <div class="panel-footer panel-nav">
                <ul class="nav nav-pills bordered nav-justified">
                    <li>
                        <a href="{{ route('breed_registry/draft/submitted', $breedDraft->id) }}">
                            {{{ $breedDraft->name }}}
                        </a>
                    </li>
                </ul>

                <a class="btn btn-block btn-primary btn-xs" href="{{ route('breed_registry/draft/submitted/revert', $breedDraft->id) }}" onclick="return confirm('Are you sure you want to edit this breed submission? You will have to resubmit it again once you\'re done editing.');">
                    Revert to Draft
                </a>
            </div>
        </div>
    </div>
    @endforeach

    @if($rejectedDrafts->isEmpty())
    <div class="col-md-12">
        <p>You do not have any rejected breeds.</p>
    </div>
    @endif
</div>

<h2>Extinct</h2>

<div class="row">
    @foreach($extinctDrafts as $breedDraft)
    <div class="col-xs-6 col-md-3">
        <div class="panel panel-default">
            @if($breedDraft->hasImage())
            <div class="panel-body">
                <a href="{{ route('breed_registry/draft/submitted', $breedDraft->id) }}">
                    <img src="{{ asset($breedDraft->getImageUrl()) }}" alt="Breed Image" title="Breed Image" class="img-responsive center-block" />
                </a>
            </div>
            @endif

            <div class="panel-footer panel-nav">
                <ul class="nav nav-pills bordered nav-justified">
                    <li>
                        <a href="{{ route('breed_registry/draft/submitted', $breedDraft->id) }}">
                            {{{ $breedDraft->name }}}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endforeach

    @if($extinctDrafts->isEmpty())
    <div class="col-md-12">
        <p>You do not have any extinct breeds.</p>
    </div>
    @endif
</div>

<h2>Accepted</h2>

<div class="row">
    @foreach($breeds as $breed)
    <div class="col-xs-6 col-md-3">
        <div class="panel panel-default">
            @if($breed->hasImage())
            <div class="panel-body">
                @if($breed->isActive())
                <a href="{{ route('breed_registry/breed', $breed->id) }}">
                    <img src="{{ asset($breed->getImageUrl()) }}" alt="Breed Image" title="Breed Image" class="img-responsive center-block" />
                </a>
                @else
                <img src="{{ asset($breed->getImageUrl()) }}" alt="Breed Image" title="Breed Image" class="img-responsive center-block" />
                @endif
            </div>
            @endif

            <div class="panel-footer panel-nav">
                <ul class="nav nav-pills bordered nav-justified">
                    <li>
                        @if($breed->isActive())
                        <a href="{{ route('breed_registry/breed', $breed->id) }}">
                            {{{ $breed->name }}}
                        </a>
                        @else
                        <a disabled>
                            {{{ $breed->name }}} <em>(Inactive)</em>
                        </a>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endforeach

    @if($breeds->isEmpty())
    <div class="col-md-12">
        <p>You do not have any accepted breeds.</p>
    </div>
    @endif
</div>

@stop

{{-- JS assets --}}
@section('js_assets')
@parent
<script type="text/javascript" src="{{ asset('assets/js/breed_registry.js') }}"></script>
@stop
