<?php

class UserNotification extends Eloquent {

    const TYPE_INFO    = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_DANGER  = 'danger';

    public static $readParameter = 'rdnotif';

    protected $guarded = array('id');

    public function getUnseenAttribute($unseen)
    {
        return (bool) $unseen;
    }

    public function getUnreadAttribute($unread)
    {
        return (bool) $unread;
    }

    public function getPersistentAttribute($persistent)
    {
        return (bool) $persistent;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeRead($query)
    {
        return $query->where('unread', false);
    }
    
    public function scopeWhereRead($query)
    {
        return $this->scopeRead($query);
    }
    
    public function scopeUnread($query)
    {
        return $query->where('unread', true);
    }
    
    public function scopeWhereUnread($query)
    {
        return $this->scopeUnread($query);
    }

    public function scopeSeen($query)
    {
        return $query->where(function($query)
            {
                $query->where('unseen', false)->orWhere('persistent', true);
            });
    }
    
    public function scopeWhereSeen($query)
    {
        return $this->scopeSeen($query);
    }

    public function scopeUnseen($query)
    {
        return $query->where(function($query)
            {
                $query->where('unseen', true)->orWhere('persistent', true);
            });
    }
    
    public function scopeWhereUnseen($query)
    {
        return $this->scopeUnseen($query);
    }

    public function scopePersistent($query)
    {
        return $query->where('persistent', true);
    }
    
    public function scopeWherePersistent($query)
    {
        return $this->scopePersistent($query);
    }

    public function scopeTemporary($query)
    {
        return $query->where('persistent', false);
    }
    
    public function scopeWhereTemporary($query)
    {
        return $this->scopeTemporary($query);
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

    public function types()
    {
        return array(
            UserNotification::TYPE_INFO, 
            UserNotification::TYPE_SUCCESS, 
            UserNotification::TYPE_WARNING, 
            UserNotification::TYPE_DANGER, 
        );
    }

    public function isUnseen()
    {
        return $this->unseen;
    }

    public function isSeen()
    {
        return ( ! $this->unseen);
    }

    public function isUnread()
    {
        return $this->unread;
    }

    public function isRead()
    {
        return ( ! $this->unread);
    }

    public function isPersistent()
    {
        return $this->persistent;
    }

    public function isLazy()
    {
        return ( ! $this->persistent);
    }

    public function querystring($id = NULL)
    {
        return UserNotification::$readParameter.'='.(is_null($id) ? $this->id : $id);
    }

}
