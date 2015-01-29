<?php

class CommunityChallengeEntry extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getWinnerAttribute($winner)
    {
        return (bool) $winner;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereWinner($query)
    {
        return $query->where('winner', true);
    }

    public function scopeWhereLoser($query)
    {
        return $query->where('winner', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the community challenge.
     *
     * @return CommunityChallenge
     */
    public function communityChallenge()
    {
        return $this->belongsTo('CommunityChallenge', 'community_challenge_id', 'id');
    }

    /**
     * Return the dog.
     *
     * @return Dog
     */
    public function dog()
    {
        return $this->belongsTo('Dog', 'dog_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isWinner()
    {
        return $this->winner;
    }

    public function isLoser()
    {
        return ( ! $this->winner);
    }

}
