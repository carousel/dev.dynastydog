<?php

class UserContestType extends Eloquent {

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the creator.
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }


    /*
    |--------------------------------------------------------------------------
    | One To Many Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * All prerequisites
     *
     * @return Collection of ContestPrerequisites
     */
    public function prerequisites()
    {
        return $this->hasMany('UserContestTypePrerequisite', 'contest_type_id', 'id');
    }

    /**
     * All requirements
     *
     * @return Collection of ContestRequirements
     */
    public function requirements()
    {
        return $this->hasMany('UserContestTypeRequirement', 'contest_type_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function hasCompletedPrerequisites()
    {
        foreach($this->prerequisites as $prerequisite)
        {
            if ( ! $prerequisite->isComplete())
            {
                return false;
            }
        }

        return true;
    }

}
