<?php

class DogCharacteristicSymptom extends Eloquent {

    public $timestamps = false;

    protected $guarded = array('id');

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getExpressedAttribute($expressed)
    {
        return (bool) $expressed;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeWhereExpressed($query)
    {
        return $query->where('dog_characteristic_symptoms.expressed', true);
    }

    public function scopeWhereNotExpressed($query)
    {
        return $query->where('dog_characteristic_symptoms.expressed', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the dog characteristic
     *
     * @return DogCharacteristic
     */
    public function dogCharacteristic()
    {
        return $this->belongsTo('DogCharacteristic', 'dog_characteristic_id');
    }

    /**
     * Return the characteristic severity symptom
     *
     * @return CharacteristicSeveritySymptom
     */
    public function characteristicSeveritySymptom()
    {
        return $this->belongsTo('CharacteristicSeveritySymptom', 'characteristic_severity_symptom_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function isExpressed()
    {
        return $this->expressed;
    }

    public function canBeExpressed()
    {
        return $this->characteristicSeveritySymptom->canBeExpressed();
    }

    public function isLethal()
    {
        $dog = $this->dogCharacteristic->dog;

        // Check on the breed
        if ( ! is_null($dog->breed))
        {
            // Grab the umbrella characteristic
            $characteristicSeverity = $this->characteristicSeveritySymptom->characteristicSeverity;
            $characteristic = $characteristicSeverity->characteristic;

            // Check on the breed characteristic first
            $breedCharacteristic = $dog->breed->characteristics()->where('characteristic_id', $characteristic->id)->first();

            if ( ! is_null($breedCharacteristic))
            {
                // Find the severity
                $breedCharacteristicSeverity = $breedCharacteristic->severities()->where('characteristic_severity_id', $characteristicSeverity->id)->first();

                if ( ! is_null($breedCharacteristicSeverity))
                {
                    // Find the symptom
                    $breedSymptom = $breedCharacteristicSeverity->symptoms()->where('characteristic_severity_symptom_id', $this->characteristic_severity_symptom_id)->first();

                    return ( ! is_null($breedSymptom) and $breedSymptom->isLethal());
                }
            }
        }

        // If can't find breed characteristic, check against characteristic as backup
        return $this->characteristicSeveritySymptom->isLethal();
    }

    public function killDog()
    {
        $dog = $this->dogCharacteristic->dog;

        if ( ! is_null($dog->owner))
        {
            // Send a notification to the dog's owner
            $params = array(
                'symptom' => $this->characteristicSeveritySymptom->symptom->name, 
                'dog'     => $dog->nameplate(), 
                'dogUrl'  => URL::route('dog/profile', $dog->id), 
                'pronoun' => ($dog->isFemale() ? 'her' : 'his'), 
            );

            $body = Lang::get('notifications/dog.lethal_symptom.to_owner', array_map('htmlentities', array_dot($params)));
            
            $dog->owner->notify($body, UserNotification::TYPE_DANGER);
        }

        // Kill the dog
        $dog->kill();
    }

}
