<?php

class ChallengeLevel extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /**
     * All users in this level
     *
     * @return Collection of Users
     */
    public function users()
    {
        return $this->hasMany('User', 'challenge_level_id', 'id');
    }

    public function challengesNeededUntilNextLevel($totalChallenges)
    {
        $totalNeeded = DB::table('challenge_levels')
            ->where('completed_challenges', '<=', $this->completed_challenges)
            ->sum('completed_challenges');

        $nextLevelNeeded = DB::table('challenge_levels')
            ->select('completed_challenges')
            ->where('completed_challenges', '>', $this->completed_challenges)
            ->orderBy('completed_challenges', 'asc')
            ->take(1)
            ->pluck('completed_challenges');

        if (is_null($nextLevelNeeded))
        {
            $nextLevelNeeded = 0;
        }

        return ($nextLevelNeeded - ($totalChallenges - $totalNeeded));
    }

    public function getNextChallengeLevel()
    {
        return ChallengeLevel::where('completed_challenges', '>', $this->completed_challenges)->orderBy('completed_challenges', 'asc')->first();
    }

    public function getPreviousChallengeLevel()
    {
        return ReferralLevel::where('completed_challenges', '<', $this->completed_challenges)->orderBy('completed_challenges', 'desc')->first();
    }

}
