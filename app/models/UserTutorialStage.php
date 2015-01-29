<?php

class UserTutorialStage extends Eloquent {

    protected $guarded = array('id');

    protected $dates = ['completed_at'];

    public function getSeenAttribute($seen)
    {
        return (bool) $seen;
    }

    public function getDataAttribute($data)
    {
        return json_decode($data, true);
    }

    public function setDataAttribute($data)
    {
        $this->attributes['data'] = json_encode($data);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeCurrent($query)
    {
        return $query->orderBy('tutorial_stage_number', 'desc')->take(1);
    }

    public function scopeWhereCurrent($query)
    {
        return $this->scopeCurrent($query);
    }

    public function scopeComplete($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeWhereComplete($query)
    {
        return $this->scopeComplete($query);
    }

    public function scopeIncomplete($query)
    {
        return $query->whereNull('completed_at');
    }

    public function scopeWhereIncomplete($query)
    {
        return $this->scopeIncomplete($query);
    }

    public function scopeSeen($query)
    {
        return $query->where('seen', true);
    }

    public function scopeWhereSeen($query)
    {
        return $this->scopeSeen($query);
    }

    public function scopeUnseen($query)
    {
        return $query->where('seen', false);
    }

    public function scopeWhereUnseen($query)
    {
        return $this->scopeUnseen($query);
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
        return $this->belongsTo('User', 'user_id');
    }

    /**
     * Return the tutorial stage
     *
     * @return TutorialStage
     */
    public function tutorialStage()
    {
        return $this->belongsTo('TutorialStage', 'tutorial_stage_number', 'number');
    }

    public function getToDo()
    {
        $currentStage  = $this;
        $tutorialStage = $this->tutorialStage;

        return View::make('frontend.tutorial.body', compact('currentStage', 'tutorialStage'));
    }

    public function saw()
    {
        $this->seen = true;
        $this->save();
        return $this;
    }

    public function isUnseen()
    {
        return ( ! $this->seen);
    }

    public function hasSeen()
    {
        return $this->seen;
    }

    public function isIncomplete()
    {
        return is_null($this->completed_at);
    }

    public function isComplete()
    {
        return ( ! is_null($this->completed_at));
    }

}
