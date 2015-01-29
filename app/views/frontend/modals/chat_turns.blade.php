@if( ! is_null($currentUser))
    <div class="modal fade" id="model-chat-turns" tabindex="-1" role="dialog" aria-labelledby="model-chat-turns-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="model-chat-turns-label">Give Turns</h4>
                </div>
                <form role="form" method="post" action="{{ route('chat/give_turns') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="modal-body">
                        <p>Want to do something nice for your friends in chat? Give them some extra turns! When you get turns here, a link from your name will be posted in chat - every person who clicks on it will receive a turn courtesy of you, until all the turns are picked up. You can't pick these up yourself, they are for others only - but all of chat will surely love you for it!</p>

                        <div class="form-group">
                            <label for="numberOfTurns" class="control-label"><section class="text-left">Select Number of Turns:</section></label>

                                <select name="turn_package_id" class="form-control" id="numberOfTurns" required>
                                    @foreach(TurnPackage::orderBy('amount', 'asc')->get() as $turnPackage)
                                        <option value="{{ $turnPackage->id }}">
                                            {{ Dynasty::turns($turnPackage->amount) }}
                                            for
                                            {{ Dynasty::credits($turnPackage->credit_cost) }}
                                        </option>
                                    @endforeach
                                </select>
                        </div>

                        <div class="alert alert-danger">
                            <p class="text-center no-margin">
                                <big><strong>This will not give turns to you!</strong></big>
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" name="chat_give_turns">Give Turns</button>
                    </div>
                </form>
            </div>
        </div>
</div>
@endif
