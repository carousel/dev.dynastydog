{{-- <!-- start-tutorial --> --}}
@if($tutorialStage->slug == 'start-tutorial')
    <p>Welcome to Dynasty!</p>

    <p>Most of the game is a sandbox for your own goals. However, Dynasty also provides you with directed challenges to help learn the ropes and inspire you to set your own goals.</p>

    <p><strong>Please click "Start Challenge". (You need to be on the Goals page in order to do this.)</strong></p>

    <p>Don't worry, you'll get to see the dog you made at registration very, very soon!</p>

{{-- <!-- start-first-individual-challenge --> --}}
@elseif($tutorialStage->slug == 'start-first-individual-challenge')
    <p>Great! You rolled your first challenge.</p>

    <p>In Dynasty, all dogs are defined by their characteristics, like Height, Colour, and Intelligence. Look at the <strong>characteristic highlighted</strong> in the grey box:</p>

    <div class="callout callout-info">
        <ul class="list-unstyled no-margin">
            @if($challenge = Challenge::find($currentStage->data['challenge_id']))
                @foreach($challenge->characteristics()->with('characteristic')->get() as $challengeCharacteristic)
                    <li>
                        <strong>{{ $challengeCharacteristic->characteristic->name }}:</strong>
                        {{ $challengeCharacteristic->getGoalString() }}
                    </li>
                @endforeach
            @endif
        </ul>
    </div>

    <p>To complete this challenge, you need to obtain a dog that fits the required characteristic. <strong>Head over to your kennel page</strong> to see if you already have a dog that matches.</p>

{{-- <!-- find-dog-for-first-individual-challenge --> --}}
@elseif($tutorialStage->slug == 'find-dog-for-first-individual-challenge')
    <p>Dynasty is a turn-based game.</p>

    <p><strong>Every turn = {{ Str::plural('month', Config::get('game.dog.months_to_age')) }} of age. Only dogs that have been worked this turn will age</strong> - their name will turn grey.</p>

    <p>If you are familiar with other games in this genre, know that Dynasty steps away from most typical genre conventions such as feeding dogs and vet visits. There are only three things you can do with your dogs directly: <strong>test, breed, and show in contests - and each of these things counts as working the dog</strong>.</p>

    @if( ! is_null($dog = Dog::find($currentStage->data['dog_id'])))
        <p><strong>Click on your dog's name</strong> to visit {{ $dog->isFemale() ? 'her' : 'his' }} page.</p>
    @endif

{{-- <!-- visit-first-dog-page --> --}}
@elseif($tutorialStage->slug == 'visit-first-dog-page')
    @if(($challenge = Challenge::find($currentStage->data['challenge_id'])) and $challenge->isComplete())
        <p>Welcome to your dog's page!</p>

        <p><strong>All dogs are defined by their characteristics</strong>, some of which are known and some which are not and must be tested for.</p>

        <p><strong>Testing is one of the actions that takes up a dog's turn.</strong></p>

        <p>Explore your dog's page, then <strong>click on a test next to a characteristic</strong>.</p>
    @else
        <p>Welcome to your dog's page!</p>

        <p><strong>All dogs are defined by their characteristics</strong>, some of which are known and some which are not and must be tested for.</p>

        <p><strong>Testing is one of the actions that takes up a dog's turn.</strong></p>

        <p>Explore your dog's page, then <strong>click on a test next to the characteristic</strong> that you need for your challenge:</p>

        <div class="callout callout-info">
            <ul class="list-unstyled no-margin">
                @if($challenge = Challenge::find($currentStage->data['challenge_id']))
                    @foreach($challenge->characteristics()->with('characteristic')->get() as $challengeCharacteristic)
                        <li>
                            <strong>{{ $challengeCharacteristic->characteristic->name }}:</strong>
                            {{ $challengeCharacteristic->getGoalString() }}
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    @endif

{{-- <!-- first-test-dog --> --}}
@elseif($tutorialStage->slug == 'first-test-dog')
    @if( ! is_null($dog = Dog::find($currentStage->data['dog_id'])))
        <p>Great job, you just tested <a href="{{ route('dog/profile', $dog->id) }}">{{{ $dog->nameplate() }}}</a>!</p>
    @endif
    
    <p>Dogs on Dynasty have realistic life spans based on their breed, so you may or may not be able to reveal every characteristic during their lifetime.</p>

    @if( ! is_null($dogCharacteristic = DogCharacteristic::with('characteristic')->where('id', $currentStage->data['dog_characteristic_id'])->first()))
        <div class="well well-sm">
            <div class="row">
                <div class="col-xs-5 text-center">
                     <strong>{{ $dogCharacteristic->characteristic->name }}</strong>
                </div>
                <div class="col-xs-7 text-center">
                    @include('frontend/dog/_characteristic', ['dogCharacteristic' => $dogCharacteristic, 'showTests' => false])
                </div>
            </div>
        </div>
    @endif

    <br>

    <p class="text-center">Testing counts as working your dog, so go ahead and advance the turn by <strong>clicking the "Next Turn" button</strong>.</p>

{{-- <!-- first-advance-turn --> --}}
@elseif($tutorialStage->slug == 'first-advance-turn')
    @if(($challenge = Challenge::find($currentStage->data['challenge_id'])) and $challenge->isComplete())
        <p>Great job, you advanced a turn!</p>

        <p>Actions that count towards <strong>working a dog are testing, entering a contest, or breeding a bitch in heat</strong>.</p>

        <p>All dogs that have been worked last turn have now <strong>aged by {{ Str::plural('month', Config::get('game.dog.months_to_age')) }}</strong>. This means you can focus on only a few dogs for a while, and the ones you don't touch won't age.</p>

        <p>Head over to the <strong>Goals page</strong> now.</p>
    @else
        <p>Great job, you advanced a turn!</p>

        <p>Actions that count towards <strong>working a dog are testing, entering a contest, or breeding a bitch in heat</strong>.</p>

        <p>All dogs that have been worked last turn have now <strong>aged by {{ Str::plural('month', Config::get('game.dog.months_to_age')) }}</strong>. This means you can focus on only a few dogs for a while, and the ones you don't touch won't age.</p>

        <p>Make sure you've <strong>tested your dog for the required characteristic</strong>, and head back to the <strong>Goals page</strong>.</p>
    @endif

{{-- <!-- visit-first-goals --> --}}
@elseif($tutorialStage->slug == 'visit-first-goals')
    @if( ! $currentStage->data['skipped'])
        <p>Wonderful! You should now be able to <strong>submit your dog for this challenge and complete it</strong>! Go ahead and do so.</p>

        <p>If you haven’t yet tested your dog for the required characteristic, go back and do that first; you will not be able to continue the tutorial without it.</p>
    @endif

{{-- <!-- complete-first-individual-challenge --> --}}
@elseif($tutorialStage->slug == 'complete-first-individual-challenge')
    @if( ! $currentStage->data['skipped'])
        <p>Congratulations! You’ve just completed your <strong>first challenge</strong> and earned <strong>{{ Dynasty::credits($currentStage->data['credits']) }}</strong>.</p>

        <p>Credits are the currency on Dynasty - you can spend it in the <strong>Cash Shop</strong> and on various things around the site. It can be purchased through Paypal, or earned through completing challenges or referring your friends.</p>

        <p>Now, <strong>head over to the Import Dogs page</strong> to play through the cornerstone of Dynasty - breeding.</p>
    @else
        <p><strong>Go to the Import Dogs page</strong> to play through the cornerstone of Dynasty - breeding.</p>
    @endif

{{-- <!-- visit-first-import-dogs --> --}}
@elseif($tutorialStage->slug == 'visit-first-import-dogs')
    <p>This is where you get new foundation dogs, with no breeding history.</p>

    {{-- <p>You can select a 6-month-old puppy or a 2-year-old dog. A younger dog will have more time for you to test characteristics than an older one, but a 2-year-old is guaranteed to be of breeding age, which will allow you to breed faster.</p> --}}

    @if( ! is_null($dog = Dog::find($currentStage->data['dog_id'])))
        <p>You can cross-breed dogs on Dynasty, but you do not have to. Please <strong>import a 2-year-old {{ $dog->isFemale() ? 'Dog' : 'Bitch' }} of any breed. You've been given an extra import to do this.</p>
    @endif

{{-- <!-- first-import-dog --> --}}
@elseif($tutorialStage->slug == 'first-import-dog')
    <p>Congratulations, you've imported a mate for your dog! <strong>Head on over to the Kennel page to breed them.</strong></p>

{{-- <!-- visit-kennel-after-import --> --}}
@elseif($tutorialStage->slug == 'visit-kennel-after-import')
    <p>In order to breed your dogs, your <strong>bitch will need to be in heat</strong>.</p>

    <p>Bitches come into heat <strong>every few months</strong>, so you will need to test her for whatever you wish, and <strong>advance the turn after every test, until she comes into heat</strong>. The pop-up report that you see on every turn advance will tell you when she is in heat. Note: If she is currently in heat, you'll have to advance her turn until her next heat.</p>

    <p>Be careful! Bitches are only in heat for one turn, and <strong>they need to be bred right at the beginning of their turn</strong>.</p>

{{-- <!-- first-heat --> --}}
@elseif($tutorialStage->slug == 'first-heat')
    <p>Wonderful, your bitch is in heat this turn!</p>

    <p><strong>Go to your kennel page, and use the Breed Dogs pane in the left sidebar to breed your dogs.</strong></p>

    <p>Be careful! <strong>Do not test her this turn!</strong></p>

    <p>If you do, the test will take up her turn, and you will lose the opportunity to breed her now, and will have to wait until her next heat cycle.</p>

{{-- <!-- first-breeding --> --}}
@elseif($tutorialStage->slug == 'first-breeding')
    @if( ! is_null($bredBitch = Dog::find($currentStage->data['bred_bitch_id'])))
        <p>Great job, your bitch {{ $bredBitch->fullName() }} (#{{ $bredBitch->id }}) is now pregnant! On your next turn, she will give birth if the breeding took. <strong>Go on and advance to the next turn now.</strong></p>
    @endif

    <p>If the breeding didn’t take, you’ll need to repeat the process.</p>

{{-- <!-- first-litter --> --}}
@elseif($tutorialStage->slug == 'first-litter')
    <p>Congratulations on your litter! <strong>Go to your kennel page and visit the puppies now.</strong> You'll need to complete the puppies in order to see their characteristics.</p>

    <p>If you have <strong>dogs or puppies you don't want to keep</strong>, you can use the Pet Home feature on the dogs' pages to remove them from your kennel permanently. This will <strong>not break pedigrees</strong>.</p>

{{-- <!-- end-tutorial --> --}}
@elseif($tutorialStage->slug == 'end-tutorial')
    <p>Congratulations, you've gotten very far!</p>

    <p><strong>You've tested, aged, imported, and bred your dogs, and you are on your way to Dynasty stardom!</strong></p>

    <p>However, the real way to get your name remembered and to leave your mark on the game world lies in the challenging task of <strong>creating a new breed</strong>.</p>

    <p>Older dogs will eventually go <strong>infertile</strong>, so you’ll need to balance breeding decisions with how much you need to know about your dogs in order to fulfill your breeding goals.</p>

   <p>You are now well-versed in the inner workings of the game, but much is still left to discover. <strong>You've earned {{ Dynasty::turns(Config::get('game.tutorial.completion_turns')) }} for completing the tutorial</strong> -  you can explore the rest of the game on your own, or <strong>head back to the goals page and roll another challenge</strong>.</p>

@endif