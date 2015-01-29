<?php

class BannedIp extends Eloquent {

    public $primaryKey = 'ip';

    protected $guarded = [];

    public function users()
    {
    	return User::where('ip_banned', true)->having(DB::raw("IFNULL(last_login_ip, created_ip)"), '=', $this->ip);
    }

}
