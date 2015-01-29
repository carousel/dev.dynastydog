<?php

class UserGoal extends Eloquent {

    protected $guarded = array('id');

    protected $dates = ['completed_at'];

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    |
    |
    */

    public function setBodyAttribute($body)
    {
        $this->attributes['body'] = Purifier::clean($body);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereIncomplete($query)
    {
        return $query->whereNull('completed_at');
    }

    public function scopeWhereComplete($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the user.
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isIncomplete()
    {
        return is_null($this->completed_at);
    }

    public function isComplete()
    {
        return ( ! is_null($this->completed_at));
    }

}
