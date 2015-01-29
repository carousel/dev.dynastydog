@if( ! is_null($currentUser) and $currentUser->inTutorial())
    {{ ($currentStage = $currentUser->tutorialStages()->current()->first()) ? '' : '' }}
    <div class="modal fade" id="tutorial-current-stage" tabindex="-1" role="dialog" aria-labelledby="tutorial-current-stage-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="tutorial-current-stage-label">
                        Tutorial Step #{{$currentStage->tutorial_stage_number}}
                    </h4>
                </div>

                <div class="modal-body">
                    {{ $currentStage->getToDo() }}
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Okay, Got It</button>
                </div>
            </div>
        </div>
    </div>

    @if($currentUser->tutorialStages()->current()->first()->isUnseen())
        {{ $currentStage->saw() ? '' : '' }}
        <script type="text/javascript">$(document).ready(function(){$('#tutorial-current-stage').modal('show');});</script>
    @endif
@endif
