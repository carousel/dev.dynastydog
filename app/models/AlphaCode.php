<?php

class AlphaCode extends Eloquent {

    public $primaryKey = 'code';

    protected $guarded = [];

    /**
     * All users signed up with this alpha code
     *
     * @return Collection of Users
     */
    public function users()
    {
        return $this->hasMany('User', 'registered_alpha_code', 'code');
    }

    public static function generateCode()
    {
        return md5(uniqid(rand(), true));
    }

    public function hasRoom()
    {
        return ($this->population < $this->capacity);
    }

    public function isAtCapacity()
    {
        return ( ! $this->hasRoom());
    }

}
