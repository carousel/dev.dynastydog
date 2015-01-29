<?php namespace Controllers\Admin;

use AdminController;
use View;
use DB;
use Carbon;
use Config;
use UserCreditTransaction;
use CreditPackage;
use CommunityChallenge;
use TutorialStage;

class DashboardController extends AdminController {

    private $ignoreUserIds = [ 1, 2];

    public function getIndex()
    {
        $totalAccounts      = $this->_totalAccounts();
        $totalDogs          = $this->_totalDogs();
        $averageReferrals   = $this->_averageReferrals();
        $newRegistrations   = $this->_newRegistrations();
        $newUserRetention   = $this->_newUserRetention();
        $averageNewbieDropOffTime = $this->_averageNewbieDropOffTime();
        $grossRevenue       = $this->_grossRevenue();
        $lifetimeValue      = $this->_lifetimeValue();
        $lifetimeNetworkValue = $this->_lifetimeNetworkValue();
        $weeklyRevenue      = $this->_weeklyRevenue();
        $paymentWeeks       = $this->_paymentWeeks();
        $averageWeeklyRevenue = $this->_averageWeeklyRevenue();
        $revenueBySource    = $this->_revenueBySource();
        $perksBought        = $this->_perksBought();
        $creditPackagesBought = $this->_creditPackagesBought();
        $activeUsers        = $this->_activeUsers();
        $upgradedUsers      = $this->_upgradedUsers();
        $activeUpgradedPopulation = $this->_activeUpgradedPopulation();
        $onlineUsers        = $this->_onlineUsers();
        $weeklyCreditsGifted = $this->_weeklyCreditsGifted();
        $weeklyTurnsThrown  = $this->_weeklyTurnsThrown();
        $weeklyTurnThrowingInstances = $this->_weeklyTurnThrowingInstances();
        $longGoneRate       = $this->_longGoneRate();
        $averageLifetime    = $this->_averageLifetime();
        $averageLifetimeNewbless = $this->_averageLifetimeNewbless();
        $dropOffRate        = $this->_dropOffRate();
        $averagePlaySession = $this->_averagePlaySession();
        $mostAbandonedPage  = $this->_mostAbandonedPage();
        $mostOftenAbandonedPage = $this->_mostOftenAbandonedPage();
        $weeklyForumPosts   = $this->_weeklyForumPosts();
        $weeklyAnimalsWorked = $this->_weeklyAnimalsWorked();
        $weeklyAnimalsBred  = $this->_weeklyAnimalsBred();
        $weeklyDogsImported = $this->_weeklyDogsImported();
        $weeklyDogsCustomImported = $this->_weeklyDogsCustomImported();
        $weeklyOnlineUsers  = $this->_weeklyOnlineUsers();
        $neverLoggedIn      = $this->_neverLoggedIn();
        $contestsCreatedThisWeek = $this->_contestsCreatedThisWeek();
        $weeklyContestsCreated = $this->_weeklyContestsCreated();
        $averageContestsPerActiveUser = $this->_averageContestsPerActiveUser();
        $mostAbandonedNewbiePage = $this->_mostAbandonedNewbiePage();
        $creditsFromChallenges = $this->_creditsFromChallenges();
        $communityChallenges = $this->_communityChallenges();
        $totalCompletedTutorials = $this->_totalCompletedTutorials();
        $tutorialStages     = $this->_tutorialStages();

        // Show the page
        return View::make('admin/dashboard/index', compact(
            'totalAccounts', 'totalDogs', 'averageReferrals', 
            'newRegistrations', 'newUserRetention', 
            'averageNewbieDropOffTime', 'grossRevenue', 'lifetimeValue', 
            'lifetimeNetworkValue', 'weeklyRevenue', 'paymentWeeks', 
            'averageWeeklyRevenue', 'revenueBySource', 'perksBought', 
            'creditPackagesBought', 'activeUsers', 'upgradedUsers', 
            'activeUpgradedPopulation', 'onlineUsers', 'weeklyCreditsGifted', 
            'weeklyTurnsThrown', 'weeklyTurnThrowingInstances', 
            'longGoneRate', 'averageLifetime', 'averageLifetimeNewbless', 
            'dropOffRate', 'averagePlaySession', 'mostAbandonedPage', 
            'mostOftenAbandonedPage', 'weeklyForumPosts', 
            'weeklyAnimalsWorked', 'weeklyAnimalsBred', 'weeklyDogsImported', 
            'weeklyDogsCustomImported', 'weeklyOnlineUsers', 'neverLoggedIn', 
            'contestsCreatedThisWeek', 'weeklyContestsCreated', 
            'averageContestsPerActiveUser', 'mostAbandonedNewbiePage', 
            'creditsFromChallenges', 'communityChallenges', 
            'totalCompletedTutorials', 'tutorialStages'
        ));
    }

    private function _formatDaysHoursMinutes($time)
    {
        $time = (int) $time;

        $days  = floor($time / (Carbon::SECONDS_PER_MINUTE * Carbon::MINUTES_PER_HOUR * Carbon::HOURS_PER_DAY));
        $time -= ($days * (Carbon::SECONDS_PER_MINUTE * Carbon::MINUTES_PER_HOUR * Carbon::HOURS_PER_DAY));

        $hours = floor($time / (Carbon::SECONDS_PER_MINUTE * Carbon::MINUTES_PER_HOUR));
        $time -= ($hours * Carbon::SECONDS_PER_MINUTE * Carbon::MINUTES_PER_HOUR);

        $minutes  = round($time / Carbon::SECONDS_PER_MINUTE);
        $time    -= $minutes;

        return array(
            'days'    => $days, 
            'hours'   => $hours, 
            'minutes' => $minutes, 
        );
    }

    private function _orderWeeklyPercents($a, $b)
    {
        if ($a['weekly']['percent'] == $b['weekly']['percent'])
        {
            return 0;
        }

        return ($a['weekly']['percent'] < $b['weekly']['percent']) ? 1 : -1;
    }

    private function _arrayMedian($arr)
    {
        $count = count($arr);

        if ($count == 0)
        {
            return 0;
        }
        else if ($count % 2 == 0) // Even
        {
            $low  = floor($count / 2);
            $high = ceil($count / 2);

            return round(($arr[$low] + $arr[$high]) / 2, 2);
        }
        else
        {
            return $arr[$count / 2];
        }
    }

    private function _totalAccounts()
    {
        return DB::table('users')->count();
    }

    private function _totalDogs()
    {
        return DB::table('dogs')->count();
    }

    private function _averageReferrals()
    {
        return round(DB::table('users')->avg('total_referrals'), 2);
    }

    private function _newRegistrations()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        return DB::table('users')->where('created_at', '>=', $sevenDaysAgo)->count();
    }

    private function _newUserRetention()
    {
        $twoDaysAgo      = Carbon::now()->subDays(2)->toDateTimeString();
        $sevenDaysAgo    = Carbon::now()->subDays(7)->toDateTimeString();
        $fourteenDaysAgo = Carbon::now()->subDays(14)->toDateTimeString();

        // Get active newbie accounts
        $totalActiveNewbies = DB::table('users')
            ->where('created_at', '<=', $sevenDaysAgo)
            ->where('created_at', '>=', $fourteenDaysAgo)
            ->whereNotNull('last_action_at')
            ->where('last_action_at', '>=', $twoDaysAgo)
            ->count();

        // Get active newbie
        $totalNewbies = DB::table('users')
            ->where('created_at', '<=', $sevenDaysAgo)
            ->where('created_at', '>=', $fourteenDaysAgo)
            ->count();

        $percent = round($totalNewbies == 0 ? 0 : ($totalActiveNewbies / $totalNewbies) * 100);

        return array(
            'percent' => $percent, 
            'total'   => $totalActiveNewbies, 
        );
    }

    private function _averageNewbieDropOffTime()
    {
        $sevenDaysAgo    = Carbon::now()->subDays(7)->toDateTimeString();
        $fourteenDaysAgo = Carbon::now()->subDays(14)->toDateTimeString();

        $seconds = DB::table('users')
            ->whereNotNull('last_action_at')
            ->where('created_at', '<=', $fourteenDaysAgo)
            ->whereRaw("TIMESTAMPDIFF(DAY, last_action_at, created_at) <= 7")
            ->avg(DB::raw('TIME_TO_SEC(TIMEDIFF(last_action_at, created_at))'));

        return $this->_formatDaysHoursMinutes($seconds);
    }

    private function _grossRevenue()
    {
        return round(DB::table('payments')->sum('payment_gross'), 2);
    }

    private function _lifetimeValue()
    {
        $grossRevenue  = $this->_grossRevenue();
        $totalAccounts = $this->_totalAccounts();

        return round($totalAccounts == 0 ? 0 : $grossRevenue / $totalAccounts, 2);
    }

    private function _lifetimeNetworkValue()
    {
        $lifetimeValue    = $this->_lifetimeValue();
        $averageReferrals = $this->_averageReferrals();

        return round($lifetimeValue + ($averageReferrals * $lifetimeValue), 2);
    }

    private function _weeklyRevenue()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        // Get all revenue within the past week
        return round(DB::table('payments')->where('payment_date', '>=', $sevenDaysAgo)->sum('payment_gross'), 2);
    }

    private function _paymentWeeks()
    {
        // Get the earliest date
        $minPaymentDate = Config::get('game.start_payment_weeks');

        return Carbon::parse($minPaymentDate)->diffInWeeks();
    }

    private function _averageWeeklyRevenue()
    {
        $grossRevenue = $this->_grossRevenue();
        $paymentWeeks = $this->_paymentWeeks();

        return round($paymentWeeks == 0 ? 0 : $grossRevenue / $paymentWeeks, 2);
    }

    private function _revenueBySource()
    {
        $sevenDaysAgo  = Carbon::now()->subDays(7)->toDateTimeString();
        $thirtyDaysAgo = Carbon::now()->subDays(30)->toDateTimeString();
        $oneHundredEightyDaysAgo = Carbon::now()->subDays(180)->toDateTimeString();

        // Get the gross revenue for all campaign codes this week
        $totalWeeklyGross = DB::table('users')
            ->join('user_payments', 'user_payments.user_id', '=', 'users.id')
            ->join('payments', 'payments.id', '=', 'user_payments.payment_id')
            ->where('payments.payment_date', '>=', $sevenDaysAgo)
            ->whereRaw("LENGTH(users.campaign_code) > 0")
            ->sum('payments.payment_gross');

        // Get the gross revenue for all campaign codes this month
        $totalMonthlyGross = DB::table('users')
            ->join('user_payments', 'user_payments.user_id', '=', 'users.id')
            ->join('payments', 'payments.id', '=', 'user_payments.payment_id')
            ->where('payments.payment_date', '>=', $thirtyDaysAgo)
            ->whereRaw("LENGTH(users.campaign_code) > 0")
            ->sum('payments.payment_gross');

        // Get the gross revenue for all campaign codes this half year
        $totalHalfYearlyGross = DB::table('users')
            ->join('user_payments', 'user_payments.user_id', '=', 'users.id')
            ->join('payments', 'payments.id', '=', 'user_payments.payment_id')
            ->where('payments.payment_date', '>=', $oneHundredEightyDaysAgo)
            ->whereRaw("LENGTH(users.campaign_code) > 0")
            ->sum('payments.payment_gross');

        // Get all of the used campaign codes
        $campaignCodes = DB::table('users')
            ->whereRaw("LENGTH(users.campaign_code) > 0")
            ->groupBy('campaign_code')
            ->lists('campaign_code');

        $stats = [];

        foreach($campaignCodes as $campaignCode)
        {
            // Get for the week
            $weeklyGross = DB::table('users')
                ->join('user_payments', 'user_payments.user_id', '=', 'users.id')
                ->join('payments', 'payments.id', '=', 'user_payments.payment_id')
                ->where('users.campaign_code', '=', $campaignCode)
                ->where('payments.payment_date', '>=', $sevenDaysAgo)
                ->sum('payments.payment_gross');

            // Get for the month
            $monthlyGross = DB::table('users')
                ->join('user_payments', 'user_payments.user_id', '=', 'users.id')
                ->join('payments', 'payments.id', '=', 'user_payments.payment_id')
                ->where('users.campaign_code', '=', $campaignCode)
                ->where('payments.payment_date', '>=', $thirtyDaysAgo)
                ->sum('payments.payment_gross');

            // Get for this half year
            $halfYearlyGross = DB::table('users')
                ->join('user_payments', 'user_payments.user_id', '=', 'users.id')
                ->join('payments', 'payments.id', '=', 'user_payments.payment_id')
                ->where('users.campaign_code', '=', $campaignCode)
                ->where('payments.payment_date', '>=', $oneHundredEightyDaysAgo)
                ->sum('payments.payment_gross');

            $weeklyPercent  = round($totalWeeklyGross == 0 ? 0 : ($weeklyGross / $totalWeeklyGross) * 100, 2);
            $monthlyPercent = round($totalMonthlyGross == 0 ? 0 : ($monthlyGross / $totalMonthlyGross) * 100, 2);
            $halflyPercent  = round($totalHalfYearlyGross == 0 ? 0 : ($halfYearlyGross / $totalHalfYearlyGross) * 100, 2);

            $stats[] = array(
                'campaign_code' => $campaignCode, 
                'weekly' => array(
                    'percent' => $weeklyPercent, 
                    'amount'  => $weeklyGross, 
                    'high'    => ($weeklyPercent >= 75), 
                    'mid'     => ($weeklyPercent >= 25 and $weeklyPercent < 75), 
                    'low'     => ($weeklyPercent < 25), 
                ), 
                'monthly' => array(
                    'percent' => $monthlyPercent, 
                    'amount'  => $monthlyGross, 
                    'high'    => ($monthlyPercent >= 75), 
                    'mid'     => ($monthlyPercent >= 25 and $monthlyPercent < 75), 
                    'low'     => ($monthlyPercent < 25), 
                ), 
                'halfly' => array(
                    'percent' => $halflyPercent, 
                    'amount'  => $halfYearlyGross, 
                    'high'    => ($halflyPercent >= 75), 
                    'mid'     => ($halflyPercent >= 25 and $halflyPercent < 75), 
                    'low'     => ($halflyPercent < 25), 
                ), 
            );
        }

        usort($stats, array($this, '_orderWeeklyPercents'));

        return $stats;
    }

    private function _perksBought()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();
        $paymentWeeks = $this->_paymentWeeks();

        // Get the gross credits spent
        $totalGross = DB::table('user_credit_transactions')->sum('amount');

        $averageWeeklyTotalGross = ($paymentWeeks == 0) ? 0 : $totalGross / $paymentWeeks;

        // Get the gross credits spent for all perks this week
        $totalWeeklyGross = DB::table('user_credit_transactions')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->whereNotIn('user_id', $this->ignoreUserIds)
            ->sum('amount');

        // Get all of the perks bought
        $purchasedCreditTransactionTypes = DB::table('user_credit_transactions')
            ->whereNotIn('user_id', $this->ignoreUserIds)
            ->groupBy('type')
            ->lists('type');

        // Get the all perks in model
        $possibleCreditTransactionTypes = UserCreditTransaction::types();

        $creditTransactionTypes = array_unique(array_merge($purchasedCreditTransactionTypes, $possibleCreditTransactionTypes));

        $stats = [];

        foreach($creditTransactionTypes as $creditTransactionType)
        {
            // Get for the week
            $weeklyGross = DB::table('user_credit_transactions')
                ->where('type', $creditTransactionType)
                ->where('created_at', '>=', $sevenDaysAgo)
                ->whereNotIn('user_id', $this->ignoreUserIds)
                ->sum('amount');

            // Get all
            $gross = DB::table('user_credit_transactions')
                ->where('type', $creditTransactionType)
                ->whereNotIn('user_id', $this->ignoreUserIds)
                ->sum('amount');

            $averageWeeklyGross   = round($paymentWeeks == 0 ? 0 : $gross / $paymentWeeks, 2);
            $weeklyPercent        = round($totalWeeklyGross == 0 ? 0 : ($weeklyGross / $totalWeeklyGross) * 100, 2);
            $averageWeeklyPercent = round($averageWeeklyTotalGross == 0 ? 0 : ($averageWeeklyGross / $averageWeeklyTotalGross) * 100, 2);

            $stats[] = array(
                'credit_transaction_type' => $creditTransactionType, 
                'weekly' => array(
                    'percent' => $weeklyPercent, 
                    'amount'  => $weeklyGross, 
                    'high'    => ($weeklyPercent >= 75), 
                    'mid'     => ($weeklyPercent >= 25 and $weeklyPercent < 75), 
                    'low'     => ($weeklyPercent < 25), 
                ), 
                'weekly_avg' => array(
                    'percent' => $averageWeeklyPercent, 
                    'amount'  => $averageWeeklyGross, 
                    'high'    => ($averageWeeklyPercent >= 75), 
                    'mid'     => ($averageWeeklyPercent >= 25 and $averageWeeklyPercent < 75), 
                    'low'     => ($averageWeeklyPercent < 25), 
                ), 
            );
        }

        usort($stats, array($this, '_orderWeeklyPercents'));

        return $stats;
    }

    private function _creditPackagesBought()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();
        $paymentWeeks = $this->_paymentWeeks();

        // Get the total credits spent
        $total = DB::table('credit_package_payments')->count();

        $averageWeeklyTotal = round($paymentWeeks == 0 ? 0 : $total / $paymentWeeks, 2);

        // Get the total packages purchased this week
        $weeklyTotal = DB::table('credit_package_payments')
            ->join('payments', 'payments.id', '=', 'credit_package_payments.payment_id')
            ->where('payments.payment_date', '>=', $sevenDaysAgo)
            ->count();

        // Get all of the credit packages
        $creditPackages = CreditPackage::all();

        $stats = [];

        foreach($creditPackages as $creditPackage)
        {
            // Get for the week
            $weeklyCreditPackageTotal = DB::table('credit_package_payments')
                ->join('payments', 'payments.id', '=', 'credit_package_payments.payment_id')
                ->where('credit_package_payments.credit_package_id', $creditPackage->id)
                ->where('payments.payment_date', '>=', $sevenDaysAgo)
                ->count();

            // Get for all
            $creditPackageTotal = DB::table('credit_package_payments')
                ->where('credit_package_id', $creditPackage->id)
                ->count();

            $averageWeeklyCreditPackageTotal = round($paymentWeeks == 0 ? 0 : $creditPackageTotal / $paymentWeeks, 2);
            $weeklyCreditPackagePercent = ($weeklyTotal == 0) ? 0 : ($weeklyCreditPackageTotal / $weeklyTotal) * 100;
            $averageWeeklyCreditPackagePercent = round($averageWeeklyTotal == 0 ? 0 : ($averageWeeklyCreditPackageTotal / $averageWeeklyTotal) * 100, 2);

            $stats[] = array(
                'package' => $creditPackage->name, 
                'weekly' => array(
                    'percent' => $weeklyCreditPackagePercent, 
                    'amount'  => $weeklyCreditPackageTotal, 
                    'high'    => ($weeklyCreditPackagePercent >= 75), 
                    'mid'     => ($weeklyCreditPackagePercent >= 25 and $weeklyCreditPackagePercent < 75), 
                    'low'     => ($weeklyCreditPackagePercent < 25), 
                ), 
                'weekly_avg' => array(
                    'percent' => $averageWeeklyCreditPackagePercent, 
                    'amount'  => $averageWeeklyCreditPackageTotal, 
                    'high'    => ($averageWeeklyCreditPackagePercent >= 75), 
                    'mid'     => ($averageWeeklyCreditPackagePercent >= 25 and $averageWeeklyCreditPackagePercent < 75), 
                    'low'     => ($averageWeeklyCreditPackagePercent < 25), 
                ), 
            );
        }

        usort($stats, array($this, '_orderWeeklyPercents'));

        return $stats;
    }

    private function _activeUsers()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        return DB::table('users')
            ->whereNotNull('last_action_at')
            ->where('last_action_at', '>=', $sevenDaysAgo)
            ->count();
    }

    private function _upgradedUsers()
    {
        $now = Carbon::now()->toDateTimeString();

        return DB::table('users')
            ->whereNotNull('upgraded_until')
            ->where('upgraded_until', '>=', $now)
            ->count();
    }

    private function _activeUpgradedPopulation()
    {
        $upgradedUsers = $this->_upgradedUsers();
        $activeUsers   = $this->_activeUsers();

        return round($activeUsers == 0 ? 0 : ($upgradedUsers / $activeUsers) * 100, 2);
    }

    private function _onlineUsers()
    {
        $average = DB::table('online_users_logs')->avg('total');
        $low     = DB::table('online_users_logs')->min('total');
        $high    = DB::table('online_users_logs')->max('total');

        return array(
            'avg'  => round($average, 2), 
            'low'  => $low, 
            'high' => $high, 
        );
    }

    private function _weeklyCreditsGifted()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        return DB::table('user_credit_transfers')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->sum('amount');
    }

    private function _weeklyTurnsThrown()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();
        $sum = 0;

        $creditTransactionTypes = DB::table('user_credit_transactions')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->where('type', 'LIKE', UserCreditTransaction::CHAT_TURN_PACKAGE.'%')
            ->lists('type');

        foreach ($creditTransactionTypes as $creditTransactionType)
        {
            $parts = explode('_', $creditTransactionType);

            // Get the last part
            $amount = (int) array_pop($parts);

            // Add it to the sum
            $sum += $amount;
        }
        
        return $sum;
    }

    private function _weeklyTurnThrowingInstances()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        return DB::table('user_credit_transactions')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->where('type', 'LIKE', UserCreditTransaction::CHAT_TURN_PACKAGE.'%')
            ->count();
    }

    private function _longGoneRate()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->toDateTimeString();

        $totalLongGoneUsers = DB::table('users')
            ->whereNotNull('last_action_at')
            ->where('last_action_at', '<', $thirtyDaysAgo)
            ->count();

        $totalAccounts = $this->_totalAccounts();

        $percent = round($totalAccounts == 0 ? 0 : ($totalLongGoneUsers / $totalAccounts) * 100, 2);

        return array(
            'total'   => $totalLongGoneUsers, 
            'percent' => $percent, 
        );
    }

    private function _averageLifetime()
    {
        $seconds = DB::table('users')
            ->whereNotNull('last_action_at')
            ->avg(DB::raw('TIME_TO_SEC(TIMEDIFF(last_action_at, created_at))'));

        return $this->_formatDaysHoursMinutes($seconds);
    }

    private function _averageLifetimeNewbless()
    {
        $seconds = DB::table('users')
            ->whereNotNull('last_action_at')
            ->whereRaw("TIMESTAMPDIFF(DAY, last_action_at, created_at) <= 7")
            ->avg(DB::raw('TIME_TO_SEC(TIMEDIFF(last_action_at, created_at))'));

        return $this->_formatDaysHoursMinutes($seconds);
    }

    private function _dropOffRate()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->toDateTimeString();

        $totalDropppedOff = DB::table('users')
            ->whereNotNull('last_action_at')
            ->where('last_action_at', '<', $thirtyDaysAgo) // July 1st < July 2nd
            ->count();

        $totalAccounts = $this->_totalAccounts();

        $percent = round($totalAccounts == 0 ? 0 : ($totalDropppedOff / $totalAccounts) * 100, 2);

        return array(
            'total'   => $totalDropppedOff, 
            'percent' => $percent, 
        );
    }

    private function _averagePlaySession()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        $seconds = DB::table('users')
            ->whereNotNull('last_action_at')
            ->whereNotNull('last_login')
            ->where('last_action_at', '>=', $sevenDaysAgo)
            ->avg(DB::raw('TIME_TO_SEC(TIMEDIFF(last_action_at, last_login))'));

        return $this->_formatDaysHoursMinutes($seconds);
    }

    private function _mostAbandonedPage()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->toDateTimeString();
        $sixtyDaysAgo  = Carbon::now()->subDays(60)->toDateTimeString();

        return DB::table('users')
            ->select('last_uri', DB::raw('COUNT(id) as total'))
            ->whereNotNull('last_action_at')
            ->where('last_action_at', '>=', $sixtyDaysAgo)
            ->where('last_action_at', '<', $thirtyDaysAgo)
            ->groupBy('last_uri')
            ->orderBy('total', 'desc')
            ->pluck('last_uri');
    }

    private function _mostOftenAbandonedPage()
    {
        $sevenDaysAgo      = Carbon::now()->subDays(7)->toDateTimeString();
        $fifteenMinutesAgo = Carbon::now()->subMinutes(15)->toDateTimeString();

        return DB::table('users')
            ->select('last_uri', DB::raw('COUNT(id) as total'))
            ->whereNotNull('last_action_at')
            ->where('last_action_at', '>=', $sevenDaysAgo)
            ->where('last_action_at', '<', $fifteenMinutesAgo)
            ->groupBy('last_uri')
            ->orderBy('total', 'desc')
            ->pluck('last_uri');
    }

    private function _weeklyForumPosts()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        return DB::table('forum_posts')->where('created_at', '>=', $sevenDaysAgo)->count();
    }

    private function _weeklyAnimalsWorked()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        return DB::table('dogs')
            ->join('users', 'users.id', '=', 'dogs.owner_id')
            ->where('dogs.worked', true)
            ->whereNotNull('users.last_action_at')
            ->where('users.last_action_at', '>=', $sevenDaysAgo)
            ->count();
    }

    private function _weeklyAnimalsBred()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        // Get all dogs from a litter than were born this week
        $weeklyTotal = DB::table('dogs')
            ->whereNotNull('litter_id')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->count();

        // Get all dogs from a litter than were born more than a week ago
        $preweeklyTotal = DB::table('dogs')
            ->whereNotNull('litter_id')
            ->where('created_at', '<', $sevenDaysAgo)
            ->count();

        $paymentWeeks = $this->_paymentWeeks();

        $averagePreweekly = round($paymentWeeks == 0 ? 0 : $preweeklyTotal / $paymentWeeks, 2);

        // Need to group by weeks
        $totals = DB::table('dogs')
            ->select(DB::raw('COUNT(id) as total'), DB::raw('YEARWEEK(created_at) as yweek'))
            ->whereNotNull('litter_id')
            ->where('created_at', '<', $sevenDaysAgo)
            ->groupBy('yweek')
            ->orderBy('total', 'asc')
            ->lists('total');

        $median = $this->_arrayMedian($totals);

        return array(
            'total'  => $weeklyTotal, 
            'avg'    => $averagePreweekly, 
            'median' => $median, 
        );
    }

    private function _weeklyDogsImported()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        // Get all dogs from a litter than were born this week
        $weeklyTotal = DB::table('dogs')
            ->whereNull('litter_id')
            ->where('custom_import', false)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->count();

        // Get all dogs from a litter than were born more than a week ago
        $preweeklyTotal = DB::table('dogs')
            ->whereNull('litter_id')
            ->where('custom_import', false)
            ->where('created_at', '<', $sevenDaysAgo)
            ->count();

        $paymentWeeks = $this->_paymentWeeks();

        $averagePreweekly = round($paymentWeeks == 0 ? 0 : $preweeklyTotal / $paymentWeeks, 2);

        // Need to group by weeks
        $totals = DB::table('dogs')
            ->select(DB::raw('COUNT(id) as total'), DB::raw('YEARWEEK(created_at) as yweek'))
            ->whereNull('litter_id')
            ->where('custom_import', false)
            ->where('created_at', '<', $sevenDaysAgo)
            ->groupBy('yweek')
            ->orderBy('total', 'asc')
            ->lists('total');

        $median = $this->_arrayMedian($totals);

        return array(
            'total'  => $weeklyTotal, 
            'avg'    => $averagePreweekly, 
            'median' => $median, 
        );
    }

    private function _weeklyDogsCustomImported()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        // Get all dogs from a litter than were born this week
        $weeklyTotal = DB::table('dogs')
            ->whereNull('litter_id')
            ->where('custom_import', true)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->count();

        // Get all dogs from a litter than were born more than a week ago
        $preweeklyTotal = DB::table('dogs')
            ->whereNull('litter_id')
            ->where('custom_import', true)
            ->where('created_at', '<', $sevenDaysAgo)
            ->count();

        $paymentWeeks = $this->_paymentWeeks();

        $averagePreweekly = round($paymentWeeks == 0 ? 0 : $preweeklyTotal / $paymentWeeks, 2);

        // Need to group by weeks
        $totals = DB::table('dogs')
            ->select(DB::raw('COUNT(id) as total'), DB::raw('YEARWEEK(created_at) as yweek'))
            ->whereNull('litter_id')
            ->where('custom_import', true)
            ->where('created_at', '<', $sevenDaysAgo)
            ->groupBy('yweek')
            ->orderBy('total', 'asc')
            ->lists('total');

        $median = $this->_arrayMedian($totals);

        return array(
            'total'  => $weeklyTotal, 
            'avg'    => $averagePreweekly, 
            'median' => $median, 
        );
    }

    private function _weeklyOnlineUsers()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        $average = DB::table('online_users_logs')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->avg('total');

        $low = DB::table('online_users_logs')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->min('total');

        $high = DB::table('online_users_logs')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->max('total');

        return array(
            'avg'  => round($average, 2), 
            'low'  => $low, 
            'high' => $high, 
        );
    }

    private function _neverLoggedIn()
    {
        $oneDayAgo     = Carbon::now()->subDays(1)->toDateTimeString();
        $thirtyDaysAgo = Carbon::now()->subDays(30)->toDateTimeString();

        $totalNoActions = DB::table('users')
            ->whereNull('last_action_at')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->where('created_at', '>=', $oneDayAgo)
            ->count();

        $totalAccounts = $this->_totalAccounts();

        $percent = round($totalAccounts == 0 ? 0 : ($totalNoActions / $totalAccounts) * 100, 2);

        return array(
            'total'   => $totalNoActions, 
            'percent' => $percent, 
        );
    }

    private function _contestsCreatedThisWeek()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        return DB::table('contests')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->count();
    }

    private function _weeklyContestsCreated()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        $weeklyTotal = $this->_contestsCreatedThisWeek();

        // Get all contests that were created more than a week ago
        $preweeklyTotal = DB::table('contests')
            ->where('created_at', '<', $sevenDaysAgo)
            ->count();

        $paymentWeeks = $this->_paymentWeeks();

        $averagePreweekly = round($paymentWeeks == 0 ? 0 : $preweeklyTotal / $paymentWeeks, 2);

        // Need to group by weeks
        $totals = DB::table('contests')
            ->select(DB::raw('COUNT(id) as total'), DB::raw('YEARWEEK(created_at) as yweek'))
            ->where('created_at', '<', $sevenDaysAgo)
            ->groupBy('yweek')
            ->orderBy('total', 'asc')
            ->lists('total');

        $median = $this->_arrayMedian($totals);

        return array(
            'total'  => $weeklyTotal, 
            'avg'    => $averagePreweekly, 
            'median' => $median, 
        );
    }

    private function _averageContestsPerActiveUser()
    {
        $contestsCreatedThisWeek = $this->_contestsCreatedThisWeek();
        $activeUsers = $this->_activeUsers();

        return round($activeUsers == 0 ? 0 : $contestsCreatedThisWeek / $activeUsers, 2);
    }

    private function _mostAbandonedNewbiePage()
    {
        $fourteenDaysAgo = Carbon::now()->subDays(14)->toDateTimeString();

        return DB::table('users')
            ->select('last_uri', DB::raw('COUNT(id) as total'))
            ->whereNotNull('last_action_at')
            ->where('last_action_at', '<=', $fourteenDaysAgo)
            ->whereRaw("TIMESTAMPDIFF(DAY, last_action_at, created_at) <= 7")
            ->groupBy('last_uri')
            ->orderBy('total', 'desc')
            ->pluck('last_uri');
    }

    private function _creditsFromChallenges()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();

        $creditsFromChallenges = DB::table('challenges')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->sum('credit_payout');

        $activeUsers = $this->_activeUsers();

        $perActiveUser = round($activeUsers == 0 ? 0 : $creditsFromChallenges / $activeUsers, 2);

        return array(
            'total'           => $creditsFromChallenges, 
            'per_active_user' => $perActiveUser, 
        );
    }

    private function _communityChallenges()
    {
        return CommunityChallenge::whereJudged()->orderBy('end_date', 'desc')->get();
    }

    private function _totalCompletedTutorials()
    {
        $fourteenDaysAgo = Carbon::now()->subDays(14)->toDateTimeString();

        // Get the last tutorial step
        $lastTutorialStage = TutorialStage::max('number');

        // Get all tutorial stages
        return DB::table('user_tutorial_stages')
            ->where('tutorial_stage_number', $lastTutorialStage)
            ->where('created_at', '>=', $fourteenDaysAgo)
            ->count();
    }

    private function _tutorialStages()
    {
        return TutorialStage::with(array('users' => function($query)
            {
                $fourteenDaysAgo = Carbon::now()->subDays(14)->toDateTimeString();

                $query
                    ->whereHas('user', function($q)
                    {
                        $oneDayAgo = Carbon::now()->subDays(1)->toDateTimeString();

                        $q->whereNotNull('last_action_at')->where('last_action_at', '<', $oneDayAgo);
                    })
                    ->whereIncomplete()
                    ->where('created_at', '>=', $fourteenDaysAgo);
            }))
            ->orderBy('number', 'asc')
            ->get();
    }

}
