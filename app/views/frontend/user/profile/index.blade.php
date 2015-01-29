@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Viewing Profile</h1>
</div>

@if($currentUser->id != $profile->id)
    <ul class="nav nav-pills nav-justified">
        <li><a class="text-info" href="{{ route('user/inbox', ['compose' => $profile->id]).'#compose' }}" data-original-title="">Send Message<br><i class="fa fa-envelope"></i></a></li>
        <li><a class="text-success" href="#" data-original-title="" data-toggle="modal" data-target="#modal_gift_credits">Gift Credits<br><i class="fa fa-certificate"></i></a></li>
        @if( ! $profile->isUpgraded())
            <li><a class="text-danger" href="#" data-original-title="" data-toggle="modal" data-target="#modal_gift_upgrade">Gift Upgrade<br><i class="fa fa-gift"></i></a></li>
        @endif
        <li><a class="text-primary" href="#" data-original-title="" data-toggle="modal" data-target="#modal_gift_turns">Gift Turns<br><i class="fa fa-arrow-right"></i></a></li>
    </ul>
@endif


<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12 text-center">
            <big>
            <strong>
            {{{ $profile->display_name }}}
            (#{{ $profile->id }})
            </strong>
            </big>
        </div>
    </div>

    <div class="row">
        @if($profile->hasAvatar())
        <div class="col-sm-3">
            <img src="{{{ $profile->avatar }}}" class="center-block img-responsive">
        </div>
        <div class="col-sm-9">
        @else
        <div class="col-sm-12">
        @endif
            <div class="row">
                <div class="col-xs-3 text-right">
                    <strong>Kennel Name</strong>
                </div>
                <div class="col-xs-9">
                    <a href="{{ route('user/kennel', $profile->id) }}" data-original-title="" title="">{{{ $profile->kennel_name }}}</a>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-3 text-right">
                    <strong>Account Status</strong>
                </div>
                <div class="col-xs-9">
                    @if ($profile->isUpgraded())
                        Upgraded
                        (Expires {{ $profile->upgraded_until->diffForHumans() }})
                    @else
                        Regular
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-xs-3 text-right">
                    <strong>Last Seen</strong>
                </div>
                <div class="col-xs-9">
                    {{ is_null($profile->last_action_at) ? '<em>Never</em>' : $profile->last_action_at->diffForHumans() }}
                </div>
            </div>

            <div class="row">
                <div class="col-xs-3 text-right">
                    <strong>Date Joined</strong>
                </div>
                <div class="col-xs-9">
                    {{ $profile->created_at->format('F jS, Y') }}
                </div>
            </div>

            <div class="row">
                <div class="col-xs-3 text-right">
                    <strong>Online?</strong>
                </div>
                <div class="col-xs-9">
                    {{ $profile->isOnline() ? 'Yes' : 'No' }}
                </div>
            </div>

            @if ($currentUser->id == $profile->id or $profile->showGifterLevel())
                <div class="row">
                    <div class="col-xs-3 text-right">
                        <strong>Gifter Level</strong>
                    </div>
                    <div class="col-xs-9">
                        @if(is_null($profile->gifterLevel))
                        <em>Unknown</em>
                        @else
                            {{ $profile->gifterLevel->title }}

                            @if ($currentUser->id == $profile->id and ! is_null($nextGifterLevel = $profile->gifterLevel->getNextGifterLevel()))
                                ({{ $nextGifterLevel->title }} in {{ $giftsNeededUntilNextLevel = $profile->gifterLevel->giftsNeededUntilNextLevel($profile->gifts_given) }} {{ Str::plural('Gift', $giftsNeededUntilNextLevel) }})
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-xs-3 text-right">
                    <strong>Created Breeds</strong>
                </div>
                <div class="col-xs-9">
                    <ul>
                        @foreach (($breeds = $profile->breeds()->whereActive()->orderBy('name', 'asc')->get()) as $breed)
                            <li><a href="{{ route('breed_registry/breed', $breed->id) }}">{{ $breed->name }}</a></li>
                        @endforeach

                        @if ( ! count($breeds))
                            <li><em>None</em></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_gift_credits" tabindex="-1" role="dialog" aria-labelledby="modal_gift_credits_label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="modal_gift_credits_label">Gift Credits</h4>
            </div>

            <form class="form-horizontal" role="form" method="post" action="{{ route('cash_shop/gift_credits') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <input type="hidden" name="credit_receiver_id" value="{{ $profile->id }}" />

                <div class="modal-body">
                    <div class="alert alert-info">
                        <p class="text-center"><strong>You have {{ Dynasty::credits($currentUser->credits) }}.</strong></p>
                    </div>

                    {{ $errors->first('credit_receiver_id', '<div class="alert alert-danger"><p class="text-center">:message</p></div>') }}

                    <div class="form-group">
                        <label for="number_of_credits_to_gift" class="col-sm-3 control-label">Number of Credits</label>
                        <div class="col-sm-9">
                            <input type="text" name="number_of_credits_to_gift" class="form-control" id="number_of_credits_to_gift" value="{{{ Input::old('number_of_credits_to_gift') }}}" required />
                            {{ $errors->first('number_of_credits_to_gift', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="sendCreditsMessage" class="col-sm-3 control-label">Message</label>
                        <div class="col-sm-9">
                            <textarea name="message_to_send_with_credits" class="form-control" id="sendCreditsMessage" rows="3">{{{ Input::old('message_to_send_with_credits') }}}</textarea>
                            {{ $errors->first('message_to_send_with_credits', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="sendCreditsSendAnonymously" class="col-sm-3 control-label">Gift Anonymously?</label>
                        <div class="col-sm-9">
                            <div class="checkbox">
                                <label for="sendCreditsSendAnonymously">
                                    <input type="checkbox" name="gift_credits_anonymously" value="yes" id="sendCreditsSendAnonymously" {{ Input::old('gift_credits_anonymously') == 'yes' ? 'checked' : '' }} />
                                    Yes
                                </label>
                            </div>
                            {{ $errors->first('gift_credits_anonymously', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" name="gift_credits" value="gift_credits" class="btn btn-primary">Gift Credits</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_gift_upgrade" tabindex="-1" role="dialog" aria-labelledby="modal_gift_upgrade_label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="modal_gift_upgrade_label">Gift Upgrade</h4>
            </div>
            <form class="form-horizontal" role="form" method="post" action="{{ route('cash_shop/gift_upgrade') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <input type="hidden" name="upgrade_receiver_id" value="{{ $profile->id }}" />

                <div class="modal-body">
                    <div class="alert alert-info">
                        <p class="text-center"><strong>You have {{ Dynasty::credits($currentUser->credits) }}.</strong></p>
                    </div>

                    @include('notes/gift_upgrade')

                    {{ $errors->first('upgrade_receiver_id', '<div class="alert alert-danger"><p class="text-center">:message</p></div>') }}

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Cost</label>
                        <div class="col-sm-9">
                            <p class="form-control-static">{{ Dynasty::credits(Config::get('game.user.upgrade_cost')) }}</p>
                            {{ $errors->first('gift_upgrade_cost', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="giftUpgradeMessage" class="col-sm-3 control-label">Message</label>
                        <div class="col-sm-9">
                            <textarea name="message_to_send_with_upgrade" class="form-control" id="giftUpgradeMessage" rows="3">{{{ Input::old('message_to_send_with_upgrade') }}}</textarea>
                            {{ $errors->first('message_to_send_with_upgrade', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="giftUpgradeSendAnonymously" class="col-sm-3 control-label">Gift Anonymously?</label>
                        <div class="col-sm-9">
                            <div class="checkbox">
                                <label for="giftUpgradeSendAnonymously">
                                    <input type="checkbox" name="gift_upgrade_anonymously" value="yes" id="giftUpgradeSendAnonymously" {{ Input::old('gift_upgrade_anonymously') == 'yes' ? 'checked' : '' }} />
                                    Yes
                                </label>
                            </div>
                            {{ $errors->first('gift_upgrade_anonymously', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="gift_upgrade" value="gift_upgrade" class="btn btn-primary">Gift 1 Month Upgrade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_gift_turns" tabindex="-1" role="dialog" aria-labelledby="modal_gift_turns_label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="modal_gift_turns_label">Gift Turns</h4>
            </div>

            <form class="form-horizontal" role="form" method="post" action="{{ route('cash_shop/gift_turns') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <input type="hidden" name="turn_receiver_id" value="{{ $profile->id }}" />

                <div class="modal-body">
                    <div class="alert alert-info">
                        <p class="text-center"><strong>You have {{ Dynasty::credits($currentUser->credits) }}.</strong></p>
                    </div>

                    {{ $errors->first('turn_receiver_id', '<div class="alert alert-danger"><p class="text-center">:message</p></div>') }}

                    <div class="form-group">
                        <label for="profile-giftturns-turns" class="col-sm-3 control-label">Number of Turns</label>
                        <div class="col-sm-9">
                            <select name="gift_turn_package_id" class="form-control" id="profile-giftturns-turns" required>
                                @foreach(TurnPackage::orderBy('amount', 'asc')->get() as $turnPackage)
                                    <option value="{{ $turnPackage->id }}" {{ Input::old('gift_turn_package_id') == $turnPackage->id ? 'selected' : '' }}>
                                    {{ Dynasty::turns($turnPackage->amount) }}
                                    (for {{ Dynasty::credits($turnPackage->credit_cost) }})
                                    </option>
                                @endforeach
                            </select>
                            {{ $errors->first('gift_turn_package_id', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="profile-giftturns-message" class="col-sm-3 control-label">Message</label>
                        <div class="col-sm-9">
                            <textarea name="message_to_send_with_turns" class="form-control" id="profile-giftturns-message" rows="3">{{{ Input::old('message_to_send_with_turns') }}}</textarea>
                            {{ $errors->first('message_to_send_with_turns', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="profile-giftturns-anonymous" class="col-sm-3 control-label">Gift Anonymously?</label>
                        <div class="col-sm-9">
                            <div class="checkbox">
                                <label for="profile-giftturns-anonymous">
                                    <input type="checkbox" name="gift_turns_anonymous" value="yes" id="profile-giftturns-anonymous" {{ Input::old('gift_turns_anonymous') == 'yes' ? 'checked' : '' }} />
                                    Yes
                                </label>
                            </div>
                            {{ $errors->first('gift_turns_anonymous', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" name="gift_turns" value="gift_turns" class="btn btn-primary">Gift Turns</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(Input::old('gift_credits', null))
<script type="text/javascript">$(window).load(function(){$('#modal_gift_credits').modal('show');});</script>
@endif

@if(Input::old('gift_upgrade', null))
<script type="text/javascript">$(window).load(function(){$('#modal_gift_upgrade').modal('show');});</script>
@endif

@if(Input::old('gift_turns', null))
<script type="text/javascript">$(window).load(function(){$('#modal_gift_turns').modal('show');});</script>
@endif

@stop
