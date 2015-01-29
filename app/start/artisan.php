<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/

Artisan::add(new MoveHouseCommand);
Artisan::add(new MoveDogsCommand);
Artisan::add(new CheckDogMaturitiesCommand);
Artisan::add(new RunContestsCommand);
Artisan::add(new RemoveContestsCommand);
Artisan::add(new JudgeContestsCommunityChallengesCommand);
Artisan::add(new ExpireLendRequestsCommand);
Artisan::add(new ExtinctBreedsCommand);
Artisan::add(new CleanChatCommand);
Artisan::add(new GiveImportsToUsersCommand);
Artisan::add(new GiveTurnsToUsersCommand);
Artisan::add(new LogOnlineUsersCommand);
