<div class="row">
    <div class="col-sm-4 text-center">
        <section class="breakable well well-sm">
            @if( ! is_null($author))
            <a href="{{ route('user/profile', $author->id) }}">
                @if($author->isAdministrator())
                <span class="text-success">{{{ $author->nameplate() }}}</span>
                @else
                {{{ $author->nameplate() }}}
                @endif
            </a>
            <br />
            @if($author->hasAvatar())
            <img src="{{{ $author->avatar }}}" class="img-responsive center-block" alt="Avatar" />
            @endif
            @else
            <em>Deleted</em>
            @endif
            
            <em>{{ $post->created_at->format('F jS, Y g:i A') }}</em>
        </section>
    </div>
    <div class="col-sm-8">
        <section class="breakable ">
            {{ nl2br($post->body) }}
        </section>

        @if( ! is_null($editor))
        <p class="text-right">
            <small><em>Last edited by <a href="{{ route('user/profile', $editor->id) }}">
                @if($editor->isAdministrator())
                <span class="text-success">{{{ $editor->nameplate() }}}</span>
                @else
                {{{ $editor->nameplate() }}}
                @endif
            </a></em></small>
        </p>
        @endif
    </div>
</div>