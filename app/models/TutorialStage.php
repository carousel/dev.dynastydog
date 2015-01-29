<?php

class TutorialStage extends Eloquent {

    public $primaryKey = 'number';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * All users in this stage
     *
     * @return Collection of Users
     */
    public function users()
    {
        return $this->hasMany('UserTutorialStage', 'tutorial_stage_number', 'number');
    }

    public function getNextTutorialStage()
    {
        return TutorialStage::where('number', '>', $this->number)->orderBy('number', 'asc')->first();;
    }

    public function getPreviousTutorialStage()
    {
        return TutorialStage::where('number', '<', $this->number)->orderBy('number', 'desc')->first();;
    }

}
