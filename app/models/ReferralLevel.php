<?php

class ReferralLevel extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /**
     * All users in this level
     *
     * @return Collection of Users
     */
    public function users()
    {
        return $this->hasMany('User', 'referral_level_id', 'id');
    }

    public function referralsNeededUntilNextLevel($totalReferrals)
    {
        $totalNeeded = DB::table('referral_levels')
            ->where('referred_users', '<=', $this->referred_users)
            ->sum('referred_users');

        $nextLevelNeeded = DB::table('referral_levels')
            ->select('referred_users')
            ->where('referred_users', '>', $this->referred_users)
            ->orderBy('referred_users', 'asc')
            ->take(1)
            ->pluck('referred_users');

        if (is_null($nextLevelNeeded))
        {
            $nextLevelNeeded = 0;
        }

        return ($nextLevelNeeded - ($totalReferrals - $totalNeeded));
    }

    public function getNextReferralLevel()
    {
        return ReferralLevel::where('referred_users', '>', $this->referred_users)->orderBy('referred_users', 'asc')->first();
    }

    public function getPreviousReferralLevel()
    {
        return ReferralLevel::where('referred_users', '<', $this->referred_users)->orderBy('referred_users', 'desc')->first();
    }

}
