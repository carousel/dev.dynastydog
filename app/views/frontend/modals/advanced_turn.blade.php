@if ( ! is_null($advancedTurnReport = Session::get('advancedTurnReport')))
    <div class="modal fade" id="advanced-turn-report-modal" tabindex="-1" role="dialog" aria-labelledby="advanced-turn-report-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="advanced-turn-report-modal-label">
                        Congratulations, you've advanced a turn!
                    </h4>
                </div>
                <div class="modal-body">
                    @if($advancedTurnReport['nothing'])
                        <p class="text-center">There is nothing significant to report!</p>
                    @else
                        @if ( isset($advancedTurnReport['heat']) and ! empty($advancedTurnReport['heat']))
                            <h5>Bitches in Heat</h5>
                            <ul>
                                @foreach($advancedTurnReport['heat'] as $inHeat)
                                    <li>{{{ $inHeat }}}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ( isset($advancedTurnReport['litter']) and ! empty($advancedTurnReport['litter']))
                            <h5>Births this Turn</h5>
                            <p>You will need to reveal all newborn puppies' characteristics by visiting each puppy's page before you can do anything with them.</p>
                            <ul>
                                @foreach($advancedTurnReport['litter'] as $litter)
                                    <li>{{{ $litter }}}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ( isset($advancedTurnReport['became_ill']) and ! empty($advancedTurnReport['became_ill']))
                            <h5> Oh no! Your dogs got sick</h5>
                            <p>These illnesses will eventually go away on their own. You cannot heal them in any way.</p>
                            <ul>
                                @foreach($advancedTurnReport['became_ill'] as $becameIll)
                                    <li>{{{ $becameIll }}}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ( isset($advancedTurnReport['healed']) and ! empty($advancedTurnReport['healed']))
                            <h5>Great! Your dogs healed</h5>
                            <ul>
                                @foreach($advancedTurnReport['healed'] as $healed)
                                    <li>{{{ $healed }}}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ( isset($advancedTurnReport['symptom']) and ! empty($advancedTurnReport['symptom']))
                            <h5>Alert! Genetic Disorders have Surfaced</h5>
                            <ul>
                                @foreach($advancedTurnReport['symptom'] as $symptom)
                                    <li>{{{ $symptom }}}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ( isset($advancedTurnReport['death']) and ! empty($advancedTurnReport['death']))
                            <h5>At the Rainbow Bridge</h5>
                            <ul>
                                @foreach($advancedTurnReport['death'] as $death)
                                    <li>{{{ $death }}}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ( isset($advancedTurnReport['infertile']) and ! empty($advancedTurnReport['infertile']))
                            <h5>Age-Related Infertility</h5>
                            <ul>
                                @foreach($advancedTurnReport['infertile'] as $infertile)
                                    <li>{{{ $infertile }}}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ( isset($advancedTurnReport['mature']) and ! empty($advancedTurnReport['mature']))
                            <h5>Coming of Age</h5>
                            <ul>
                                @foreach($advancedTurnReport['mature'] as $mature)
                                    <li>{{{ $mature }}}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ( isset($advancedTurnReport['sexual_decline']) and ! empty($advancedTurnReport['sexual_decline']))
                            <h5>Sexual Decline</h5>
                            <ul>
                                @foreach($advancedTurnReport['sexual_decline'] as $sexualDecline)
                                    <li>{{{ $sexualDecline }}}</li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Okay, Got It</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">$(document).ready(function(){$('#advanced-turn-report-modal').modal('show');});</script>
@endif
