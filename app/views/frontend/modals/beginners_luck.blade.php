@if ( ! is_null($beginnersLuck = Session::get('beginnersLuck')))
<div class="modal fade" id="beginners-luck-modal" tabindex="-1" role="dialog" aria-labelledby="beginners-luck-modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="beginners-luck-modal-label">Breed Dogs</h4>
            </div>
            <div class="modal-body">
                {{{ $beginnersLuck['dog']->fullName() }}}'s (#{{ $beginnersLuck['dog']->id }}) Fertility is {{ ( ! is_null($beginnersLuck['dogFertility']) ? $beginnersLuck['dogFertility']->formatRangedValue() : '<em>Unknown</em>') }}.
                {{{ $beginnersLuck['bitch']->fullName() }}}'s (#{{ $beginnersLuck['bitch']->id }}) Fertility is {{ ( ! is_null($beginnersLuck['bitchFertility']) ? $beginnersLuck['bitchFertility']->formatRangedValue() : '<em>Unknown</em>') }}.
                This breeding has {{ ! is_null($beginnersLuck['litter_chance']) ? 'a '.$beginnersLuck['litter_chance'] : 'an <em>Unknown</em>' }} chance of resulting in a litter. Asking a new player for some Beginner’s Luck will guarantee this litter.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                <a href="{{ route('dog/breed', [$beginnersLuck['dog']->id, $beginnersLuck['bitch']->id]) }}" class="btn btn-primary">Breed</a>
                <a href="{{ route('dog/blr', [$beginnersLuck['dog']->id, $beginnersLuck['bitch']->id]) }}" class="btn btn-success">Find New Player</a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">$(document).ready(function(){$('#beginners-luck-modal').modal('show');});</script>
@endif