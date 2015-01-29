<?php

class GifterLevel extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /**
     * All users in this level
     *
     * @return Collection of Users
     */
    public function users()
    {
        return $this->hasMany('User', 'gifter_level_id', 'id');
    }

    public function giftsNeededUntilNextLevel($giftsGiven)
    {
        // Get the next level
        $nextLevel = $this->getNextGifterLevel();

        if (is_null($nextLevel))
        {
            return 0;
        }

        return ($nextLevel->min - $giftsGiven);
    }

    public function getNextGifterLevel()
    {
        return GifterLevel::where('min', '>', $this->min)->orderBy('min', 'asc')->first();
    }

    public function getPreviousGifterLevel()
    {
        return GifterLevel::where('min', '<', $this->min)->orderBy('min', 'desc')->first();
    }

}
