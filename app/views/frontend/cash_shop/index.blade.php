@extends($layout)

{{-- Page content --}}
@section('content')

<div class="row">
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Buy Credits
                </h3>
            </div>

            <div class="panel-body">
                <div class="alert alert-info text-center">
                    <strong>You have {{ Dynasty::credits($currentUser->credits) }}</strong>
                </div>

                <form role="form" action="{{ Config::get('paypal.url') }}" method="post" target="_top">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="{{ Config::get('paypal.button') }}">
                    <div class="form-group">
                        <label for="creditPackage" class="col-sm-12 control-label"><section class="text-left">Select a Credit Package</section></label>
                        <div class="col-sm-12">
                            <select class="form-control" name="os0" id="creditPackage" required>
                            @foreach(CreditPackage::orderBy('credit_amount', 'asc')->get() as $creditPackage)
                            <option value="{{ $creditPackage->name }}">
                                {{ Dynasty::credits($creditPackage->credit_amount) }} ${{ number_format($creditPackage->cost, 2) }} USD
                            </option>
                            @endforeach
                            </select><br />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12 text-center">
                            <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                        </div>
                    </div>
                    <input type="hidden" name="on0" value="Amount">
                    <input type="hidden" name="currency_code" value="USD">
                    <input type="hidden" name="custom" value="{{ $currentUser->id }}"/>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Trade Credits for Upgrades
                </h3>
            </div>
            <div class="panel-body">
                @if($currentUser->isUpgraded())
                    <div class="alert alert-danger text-center">
                        <strong>Your upgrade expires in {{ strtolower(carbon_intervalforhumans($currentUser->upgraded_until)) }}.</strong><br />
                        You can get multiple upgrades at a time - each one extends your upgrade by 30 days.
                    </div>
                @endif

                <div class="row">
                    <div class="col-sm-4">
                        <p><img src="{{ asset('assets/img/dach.png') }}" class="img-responsive center-block" alt="[dog]" title="Upgrade today!" /></p>
                        <form class="form-inline" role="form" method="post" action="{{ route('cash_shop/purchase_upgrade') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <p class="text-center">
                                <button type="submit" name="upgrade_account" class="btn btn-block btn-success">1 Month Upgrade</button>
                                <strong>Cost:</strong> {{ Dynasty::credits(Config::get('game.user.upgrade_cost')) }}<br />
                                <strong>
                                    <a data-toggle="tooltip" data-html="true" data-placement="right" title="Upgrading another player will give you 10 free credits towards your next upgrade."><i class="fa fa-question-circle"></i></a>
                                    Banked Credits:
                                </strong> {{ Dynasty::credits($currentUser->banked_credits) }}
                            </p>
                        </form>
                    </div>
                    <div class="col-sm-8">
                        <div class="row">
                            <div class="col-xs-12">
                                <p>Upgrade your account in order to get access to the following features and perks:</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-11 col-xs-offset-1">
                                <ul class="list-unstyled">
                                    <li>
                                        Summary tab on Dog's Pages
                                        <a data-toggle="tooltip" data-html="true" data-placement="right" title="<img src='{{ asset('assets/img/help/summarysmall.png') }}' alt='Summary tab' title='Summary tab' />"><i class="fa fa-question-circle"></i></a>
                                    </li>
                                    <li>
                                        Ability to Mass Test your dogs 
                                        <a data-toggle="tooltip" data-html="true" data-placement="right" title="<img src='{{ asset('assets/img/help/masstest.png') }}' alt='Mass Testing' title='Mass testing' />"><i class="fa fa-question-circle"></i></a>
                                    </li>
                                    <li>
                                        Up to 15 tabs on Kennel Page 
                                        <a data-toggle="tooltip" data-html="true" data-placement="right" title="<img src='{{ asset('assets/img/help/tabsunlim.png') }}' alt='Up to 15 kennel tabs' title='Up to 15 kennel tabs' />"><i class="fa fa-question-circle"></i></a>
                                    </li>
                                    <li>
                                        Use an avatar on the Forums 
                                    </li>
                                    <li>
                                        Have your name bolded in the Chat 
                                    </li>
                                    <li>
                                        Order your dogs by Name, Breed, Age, or ID
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Get More Turns
                </h3>
            </div>

            <div class="panel-body">
                <form class="form-horizontal" role="form" method="post" action="{{ route('cash_shop/purchase_turns') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <div class="col-sm-12">
                            <p>Want your breeding program to progress faster? You can buy more turns here.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="numberOfTurns" class="col-sm-12 control-label"><section class="text-left">Select Number of Turns</section></label>
                        <div class="col-sm-12">
                            <select name="turn_package_id" class="form-control" id="numberOfTurns" required>
                                @foreach(TurnPackage::orderBy('amount', 'asc')->get() as $turnPackage)
                                    <option value="{{ $turnPackage->id }}" {{ Input::old('turn_package_id') == $turnPackage->id ? 'selected' : '' }}>
                                    {{ Dynasty::turns($turnPackage->amount) }}
                                    (for {{ Dynasty::credits($turnPackage->credit_cost) }})
                                    </option>
                                @endforeach
                            </select>
                            {{ $errors->first('turn_package_id', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-12 text-center">
                            <button type="submit" name="get_turns" class="btn btn-block btn-primary">Get Turns</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Get More Imports
                </h3>
            </div>

            <div class="panel-body">
                <form class="form-horizontal" role="form" method="post" action="{{ route('cash_shop/purchase_imports') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <div class="col-sm-12">
                            <p>Sometimes you just need more fresh blood in your lines. They can be used whenever you wish after purchase.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="numberOfImports" class="col-sm-12 control-label"><section class="text-left">Select Number of Imports</section></label>
                        <div class="col-sm-12">
                            <select name="number_of_imports" class="form-control" id="numberOfImports" required>
                                @for($i = Config::get('game.import.min_purchase_amount'); $i <= Config::get('game.import.max_purchase_amount'); $i++)
                                    <option value="{{ $i }}" {{ $i == Input::old('number_of_imports') ? 'selected' : '' }}>
                                        {{ Dynasty::imports($i) }}
                                        (for {{ Dynasty::credits($i * Config::get('game.import.price')) }})
                                    </option>
                                @endfor
                            </select>
                            {{ $errors->first('number_of_imports', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-12 text-center">
                            <button type="submit" name="get_imports" class="btn btn-block btn-primary">Get Imports</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Get Custom Imports
                </h3>
            </div>

            <div class="panel-body">
                <form class="form-horizontal" role="form" method="post" action="{{ route('cash_shop/purchase_custom_imports') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <div class="col-sm-12">
                            <p>A custom import allows you to specify up to 3 characteristics of any import, provided they are within the breed standard for that dog. They can be used whenever you wish after purchase.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="numberOfCustomImports" class="col-sm-12 control-label"><section class="text-left">Select Number of Custom Imports</section></label>
                        <div class="col-sm-12">
                            <select name="number_of_custom_imports" class="form-control" id="numberOfCustomImports" required>
                                @for($i = Config::get('game.custom_import.min_purchase_amount'); $i <= Config::get('game.custom_import.max_purchase_amount'); $i++)
                                    <option value="{{ $i }}" {{ $i == Input::old('number_of_custom_imports') ? 'selected' : '' }}>
                                        {{ Dynasty::customImports($i) }}
                                        (for {{ Dynasty::credits($i * Config::get('game.custom_import.price')) }})
                                    </option>
                                @endfor
                            </select>
                            {{ $errors->first('number_of_custom_imports', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-12 text-center">
                            <button type="submit" name="get_custom_imports" class="btn btn-block btn-primary">Get Custom Imports</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Gift Credits
                </h3>
            </div>

            <div class="panel-body">
                <p>Send some credits as a gift to a friend or a stranger! Note: You cannot get back any credits that you gift.</p>
                <form class="form-horizontal" role="form" method="post" action="{{ route('cash_shop/gift_credits') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <label for="cashshop-giftcredits-user" class="col-sm-5 control-label">Gift To</label>
                        <div class="col-sm-7">
                            <div class="input-group">
                                <span class="input-group-addon">#</span>
                                <input type="text" name="credit_receiver_id" class="form-control" id="cashshop-giftcredits-user" value="{{{ Input::old('credit_receiver_id') }}}" placeholder="Player ID" required />
                            </div>
                            {{ $errors->first('credit_receiver_id', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cashshop-giftcredits-amount" class="col-sm-5 control-label">Number of Credits</label>
                        <div class="col-sm-7">
                            <input type="text" name="number_of_credits_to_gift" class="form-control" id="cashshop-giftcredits-amount" value="{{{ Input::old('number_of_credits_to_gift') }}}" required />
                            {{ $errors->first('number_of_credits_to_gift', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cashshop-giftcredits-message" class="col-sm-5 control-label">Message</label>
                        <div class="col-sm-7">
                            <textarea name="message_to_send_with_credits" class="form-control" id="cashshop-giftcredits-message" rows="3">{{{ Input::old('message_to_send_with_credits') }}}</textarea>
                            {{ $errors->first('message_to_send_with_credits', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cashshop-giftcredits-anonymous" class="col-sm-5 control-label">Gift Anonymously?</label>
                        <div class="col-sm-7">
                            <div class="checkbox">
                                <label for="cashshop-giftcredits-anonymous">
                                    <input type="checkbox" name="gift_credits_anonymously" value="yes" id="cashshop-giftcredits-anonymous" {{ Input::old('gift_credits_anonymously') == 'yes' ? 'checked' : '' }} />
                                    Yes
                                </label>
                            </div>
                            {{ $errors->first('gift_credits_anonymously', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-4 text-right">
                            <button type="submit" name="send_credits" class="btn btn-primary">Gift Credits</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Gift Turns
                </h3>
            </div>

            <div class="panel-body">
                <form class="form-horizontal" role="form" method="post" action="{{ route('cash_shop/gift_turns') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <label for="cashshop-giftturns-user" class="col-sm-5 control-label">Gift To</label>
                        <div class="col-sm-7">
                            <div class="input-group">
                                <span class="input-group-addon">#</span>
                                <input type="text" name="turn_receiver_id" class="form-control" id="cashshop-giftturns-user" value="{{{ Input::old('turn_receiver_id') }}}" placeholder="Player ID" required />
                            </div>
                            {{ $errors->first('turn_receiver_id', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cashshop-giftturns-turns" class="col-sm-5 control-label">Number of Turns</label>
                        <div class="col-sm-7">
                            <select name="gift_turn_package_id" class="form-control" id="cashshop-giftturns-turns" required>
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
                        <label for="cashshop-giftturns-message" class="col-sm-5 control-label">Message</label>
                        <div class="col-sm-7">
                            <textarea name="message_to_send_with_turns" class="form-control" id="cashshop-giftturns-message" rows="3">{{{ Input::old('message_to_send_with_turns') }}}</textarea>
                            {{ $errors->first('message_to_send_with_turns', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cashshop-giftturns-anonymous" class="col-sm-5 control-label">Gift Anonymously?</label>
                        <div class="col-sm-7">
                            <div class="checkbox">
                                <label for="cashshop-giftturns-anonymous">
                                    <input type="checkbox" name="gift_turns_anonymous" value="yes" id="cashshop-giftturns-anonymous" {{ Input::old('gift_turns_anonymous') == 'yes' ? 'checked' : '' }} />
                                    Yes
                                </label>
                            </div>
                            {{ $errors->first('gift_turns_anonymous', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-4 text-right">
                            <button type="submit" name="gift_turns" class="btn btn-primary">Gift Turns</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <h3 class="panel-title">
                    Gift Upgrade
                </h3>
            </div>

            <div class="panel-body">
                <p>Want to do something nice for a friend, or even a complete stranger? Upgrade their account! The upgrade will be applied right away.</p>

                @include('notes/gift_upgrade')

                <form class="form-horizontal" role="form" method="post" action="{{ route('cash_shop/gift_upgrade') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Cost</label>
                        <div class="col-sm-8">
                            <p class="form-control-static">{{ Dynasty::credits(Config::get('game.user.upgrade_cost')) }}</p>
                            {{ $errors->first('gift_upgrade_cost', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cashshop-giftupgrade-user" class="col-sm-4 control-label">Gift To</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <span class="input-group-addon">#</span>
                                <input type="text" name="upgrade_receiver_id" class="form-control" id="cashshop-giftupgrade-user" value="{{{ Input::old('upgrade_receiver_id') }}}" placeholder="Player ID" required />
                            </div>
                            {{ $errors->first('upgrade_receiver_id', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cashshop-giftupgrade-message" class="col-sm-4 control-label">Message</label>
                        <div class="col-sm-8">
                            <textarea name="message_to_send_with_upgrade" class="form-control" id="cashshop-giftupgrade-message" rows="3">{{{ Input::old('message_to_send_with_upgrade') }}}</textarea>
                            {{ $errors->first('message_to_send_with_upgrade', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cashshop-giftupgrade-anonymous" class="col-sm-5 control-label">Gift Anonymously?</label>
                        <div class="col-sm-7">
                            <div class="checkbox">
                                <label for="cashshop-giftupgrade-anonymous">
                                    <input type="checkbox" name="gift_upgrade_anonymously" value="yes" id="cashshop-giftupgrade-anonymous" {{ Input::old('gift_upgrade_anonymously') == 'yes' ? 'checked' : '' }} />
                                    Yes
                                </label>
                            </div>
                            {{ $errors->first('gift_upgrade_anonymously', '<span class="help-block">:message</span>') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-4 text-right">
                            <button type="submit" name="gift_upgrade" class="btn btn-primary">Gift 1 Month Upgrade</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@stop
