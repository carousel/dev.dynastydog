<?php

class KennelGroup extends Eloquent {

    const CEMETERY = 0;
    const PRIMARY  = 1;
    const EXTRA    = 2;

    const DOG_ORD_ID    = 0;
    const DOG_ORD_NAME  = 1;
    const DOG_ORD_BREED = 2;
    const DOG_ORD_AGE   = 3;

    public $timestamps = false;

    protected $guarded = array('id');

    public function setDescriptionAttribute($description)
    {
        $this->attributes['description'] = Purifier::clean($description);
    }

    /**
     * Return the user
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id');
    }

    /**
     * All dogs in this kennel group
     *
     * @return Collection of Dogs
     */
    public function dogs()
    {
        return $this->hasMany('Dog', 'kennel_group_id', 'id');
    }

    public function scopeWhereCemetery($query)
    {
        return $query->where('type_id', KennelGroup::CEMETERY);
    }

    public function scopeWhereNotCemetery($query)
    {
        return $query->where('type_id', '<>', KennelGroup::CEMETERY);
    }

    public static function getDogOrders()
    {
        return array(
            KennelGroup::DOG_ORD_AGE   => 'Age', 
            KennelGroup::DOG_ORD_BREED => 'Breed', 
            KennelGroup::DOG_ORD_ID    => 'ID', 
            KennelGroup::DOG_ORD_NAME  => 'Name', 
        );
    }

    public function isPrimary()
    {
        return ($this->type_id == KennelGroup::PRIMARY);
    }

    public function isExtra()
    {
        return ($this->type_id == KennelGroup::EXTRA);
    }

    public function isCemetery()
    {
        return ($this->type_id == KennelGroup::CEMETERY);
    }

    public function isNotCemetery()
    {
        return ($this->type_id != KennelGroup::CEMETERY);
    }

    public function canBeEdited()
    {
        return $this->isNotCemetery();
    }

    public function canBeDeleted()
    {
        return $this->isNotCemetery();
    }

    public function isEmpty()
    {
        return ($this->dogs()->count() < 1);
    }

    public function getNeighbors($dog)
    {
        $orderedDogs = $dog->kennelGroup->orderDogs();

        if (empty($orderedDogs))
        {
            return [$dog, $dog];
        }

        $ids = array_keys($orderedDogs);
        $key = array_search($dog->id, $ids);

        // Get the previous dog
        $previousKey = $key - 1;

        if ( ! array_key_exists($previousKey, $ids))
        {
            $previousKey = count($ids) - 1;
        }

        $previousId = $ids[$previousKey];
        $previous = $orderedDogs[$previousId];

        // Get next dog
        $nextKey = $key + 1;

        if ( ! array_key_exists($nextKey, $ids))
        {
            $nextKey = 0;
        }

        $nextId = $ids[$nextKey];
        $next = $orderedDogs[$nextId];

        return [$previous, $next];
    }

    public function orderDogs()
    {
        $allDogs = $this->dogs()->orderByKennelGroup($this)->get();

        $dogs    = [];
        $bitches = [];
        $puppies = [];

        foreach($allDogs as $dog)
        {
            if ($dog->isPuppy())
            {
                $puppies[$dog->litter_id][] = $dog;
            }
            else if ($dog->isFemale())
            {
                $bitches[] = $dog;
            }
            else
            {
                $dogs[] = $dog;
            }
        }

        ksort($puppies);

        $all = array_merge($dogs, $bitches, array_flatten($puppies));

        $ordered = [];

        foreach($all as $dog)
        {
            $ordered[$dog->id] = $dog;
        }

        return $ordered;
    }

}
