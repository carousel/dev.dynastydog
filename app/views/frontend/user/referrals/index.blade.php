@extends($layout)



{{-- Page content --}}

@section('content')



<div class="page-header">

    <h1>Referrals</h1>

</div>



<div class="row">

    <div class="col-md-6">

        <div class="panel panel-default">

            <div class="panel-heading clearfix">

                <h3 class="panel-title">

                    Current Referrals

                </h3>

            </div>

            <div class="panel-body">



                <div class="row">

                    <div class="col-md-12">

                        <strong>Referrals to Next Level:</strong>

                        <div class="progress">

                            <div class="progress-bar" role="progressbar" aria-valuetransitiongoal="{{ $currentUser->referralLevelCompletionPercent() }}" aria-valuemin="0" aria-valuemax="100" data-label="{{ $currentUser->referralLevelProgress() }}"></div>

                        </div>

                        <script type="text/javascript">$(function () {$(".progress .progress-bar").each(function(){var e=$(this);var r=e.attr("data-label");e.progressbar({display_text:"center",use_percentage:false,amount_format:function(e,i){return(r!==undefined?r:e);}});});});</script>

                    </div>

                </div>

                <div class="row">

                    <div class="col-xs-6">

                        <div class="panel panel-default no-margin">

                            <div class="panel-sub-heading">

                                <h2 class="no-margin text-center">{{ $currentUser->total_referrals }}</h2>

                            </div>

                            <div class="panel-body text-center">

                                Total People Referred

                            </div>

                        </div>

                    </div>

                    <div class="col-xs-6">

                        <div class="panel panel-default no-margin">

                            <div class="panel-sub-heading">

                                <div class="row">

                                    <div class="col-xs-6 no-padding">

                                        <h2 class="no-margin text-right">{{ $currentUser->referralLevel->points_per_referral }}</h2>

                                    </div>

                                    <div class="col-xs-6 text-left">

                                        {{ Str::plural('Point', $currentUser->referralLevel->points_per_referral) }} per Referral

                                    </div>

                                </div>

                            </div>

                            <div class="panel-body text-center">

                                Current Reward Level

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-6">

        <div class="panel panel-default">

            <div class="panel-heading clearfix">

                <h3 class="panel-title">

                    Referral Rewards

                </h3>

            </div>

            <div class="panel-body">

                <div class="row">

                    <div class="col-sm-6">

                        Trade referral points for credits, or reset a dog’s status to unworked, allowing you to do another action with them in the same turn.

                    </div>

                    <div class="col-sm-6">

                        <div class="panel panel-default">

                            <div class="panel-sub-heading">

                                <h2 class="no-margin text-center">{{ $currentUser->referral_points }}</h2>

                            </div>

                            <div class="panel-body text-center">

                                Referral Points

                            </div>

                        </div>

                    </div>

                </div>



                <form role="form" method="post" action="{{ route('user/referrals/reset_status') }}">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                    <div class="form-group">

                        <label>Select Dog:</label>

                        <select name="dog_id" class="form-control" required>

                            @foreach($kennelGroups as $kennelGroup)

                            <optgroup label="{{{ $kennelGroup->name }}}">

                                @foreach($kennelGroup->dogs as $dog)

                                <option value="{{ $dog->id }}">{{{ $dog->nameplate() }}}</option>

                                @endforeach

                            </optgroup>

                            @endforeach



                            @if ( ! count($kennelGroups))

                            <option value="">You do not own any eligible dogs</option>

                            @endif

                        </select>

                    </div>

                    <button type="submit" name="reset_status" class="btn btn-success btn-block">

                        Reset Dog's Status (Cost: {{ Dynasty::referralPoints(Config::get('game.referral.reset_dog_status_cost')) }})

                    </button>

                </form><br />



                <form role="form" method="post" action="{{ route('user/referrals/exchange') }}">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                    <div class="form-group">

                        <label>Select Number of Credits:</label>

                        <select name="credits" class="form-control" required>

                            @for ($i = 1; $i <= 10; $i++)

                                <option value="{{ $i }}">{{ $i }}</option>

                            @endfor

                        </select>

                    </div>

                    <button type="submit" name="trade_points" class="btn btn-success btn-block">

                        Trade Points (Cost: {{ Dynasty::referralPoints(Config::get('game.referral.points_per_credit')) }} per Credit)

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>



<div class="panel panel-default">

    <div class="panel-heading clearfix">

        <h3 class="panel-title">

            Referral Information

        </h3>

    </div>

    <div class="panel-body">

        <div class="row">

            <div class="col-xs-4">

                <div class="panel panel-default">

                    <div class="panel-sub-heading">

                        <h2 class="no-margin text-center">{{ $currentUser->id }}</h2>

                    </div>

                    <div class="panel-body text-center">

                        Referral ID

                    </div>

                </div>

            </div>

            <div class="col-xs-8">

                Refer your friends and people you think might have an interest in the game to earn Referral Points! You can ask them to put your referral ID when they register, or use one of the methods below. <span class="text-info"><big><strong>To avoid abuse of the system, more than two referrals from the same IP Address will not count for referral points!</strong></big></span>

            </div>

        </div>



        <div class="callout callout-success">

            <p class="text-center">Your referral link is: <strong>{{ route('home', ['refid' => $currentUser->id]) }}</strong></p>

        </div>



        <div class="row">

            <div class="col-xs-4 text-center">

                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('home', ['refid' => $currentUser->id])) }}" target="_blank" class="btn btn-primary">

                    <i class="fa fa-facebook"></i>

                    <strong>Share on Facebook</strong>

                </a>

            </div>

            <div class="col-xs-4 text-center">

                <a href="http://twitter.com/share?text=Dynasty%20%7C%20Dog%20Breeding%20Game&url{{ urlencode(route('home', ['refid' => $currentUser->id])) }}" target="_blank" class="btn btn-info">

                    <i class="fa fa-twitter"></i>

                    <strong>Share on Twitter</strong>

                </a>

            </div>

            <div class="col-xs-4 text-center">

                <a href="http://plus.google.com/share?url={{ urlencode(route('home', ['refid' => $currentUser->id])) }}" target="_blank" class="btn btn-danger">

                    <i class="fa fa-google-plus"></i>

                    <strong>Share on Google+</strong>

                </a>

            </div>

        </div>



        <hr />



        <div class="row">

            <div class="col-xs-3">

                <img src="http://placehold.it/150x60" class="img-responsive center-block" alt="" title="" />

            </div>

            <div class="col-xs-5">

                <textarea class="form-control" rows="4"><a href="{{ route('home', ['refid' => $currentUser->id]) }}"><img src="http://placehold.it/150x60" /></a></textarea>

                <p class="text-center">HTML for the Referral Button</p>

            </div>

            <div class="col-xs-4">

                <textarea class="form-control" rows="4">[url="{{ route('home', ['refid' => $currentUser->id]) }}"][img]http://placehold.it/150x60[/img][/url]</textarea>

                <p class="text-center">BBCode for the Referral Button</p>

            </div>

        </div>



        <hr />



        <p>

            <img src="http://dynastydog.com/assets/img/bannerbanner.png" class="img-responsive center-block" alt="" title="" />

        </p>



        <div class="row">

            <div class="col-xs-6">

                <textarea class="form-control" rows="3"><a href="{{ route('home', ['refid' => $currentUser->id]) }}"><img src="http://dynastydog.com/assets/img/bannerbanner.png" /></a></textarea>

                <p class="text-center">HTML for the Referral Banner</p>

            </div>

            <div class="col-xs-6">

                <textarea class="form-control" rows="3">[url="{{ route('home', ['refid' => $currentUser->id]) }}"][img]http://dynastydog.com/assets/img/bannerbanner.png[/img][/url]</textarea>

                <p class="text-center">BBCode for the Referral Banner</p>

            </div>

        </div>

    </div>

</div>



@stop

