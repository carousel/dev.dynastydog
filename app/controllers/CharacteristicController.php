<?php

class CharacteristicController extends AuthorizedController {

    public function postDropdown()
    {
        $hidden  = false;
        $counter = (int) Input::get('counter', 0);

        // Get all characteristic categories
        $categories = CharacteristicCategory::with(array(
                'parent', 
                'characteristics' => function($query)
                    {
                        $query->whereActive()->whereVisible()->orderBy('name', 'asc');
                    }, 
            ))
            ->whereHas('characteristics', function($query)
                {
                    $query->whereActive()->whereVisible();
                })
            ->select('characteristic_categories.*')
            ->join('characteristic_categories as parent', 'parent.id', '=', 'characteristic_categories.parent_category_id')
            ->whereNotHealth()
            ->orderBy('parent.name', 'asc')
            ->orderBy('characteristic_categories.name', 'asc')
            ->get();

        $characteristicCategories = [];

        foreach($categories as $category)
        {
            $characteristicCategories[] = array(
                'name'            => $category->name, 
                'parent_name'     => $category->parent->name, 
                'characteristics' => $category->characteristics, 
            );
        }

        $selectedCharacteristic = null;

        // Show the page
        return View::make('characteristics/_dropdown', compact('hidden', 'counter', 'characteristicCategories', 'selectedCharacteristic'));
    }

    public function postProfiles()
    {
        $characteristicId = Input::get('characteristic_id');

        $characteristic = Characteristic::find($characteristicId);

        if (is_null($characteristic) or ! $characteristic->isActive() or $characteristic->isHidden() or $characteristic->isHealth())
        {
            return;
        }

        $counter = ':counter_replace';

        // Show the page
        return View::make('characteristics/_profiles', compact(
            'characteristic', 'counter'
        ));
    }

    public function postCustomImportDropdown()
    {
        $hidden  = false;
        $counter = (int) Input::get('counter');
        $breedId = (int) Input::get('breed');

        // Find the breed
        $breed = Breed::whereActive()->whereImportable()->where('id', $breedId)->first();

        if (is_null($breed))
        {
            throw new Dynasty\Breeds\Exceptions\NotFoundException;
        }

        // Get all characteristic IDs found in that breed
        $foundCharacteristicIds = $breed->characteristics()->whereVisible()->lists('characteristic_id');

        // Always add -1
        $foundCharacteristicIds[] = -1;

        // Get all characteristic categories
        $categories = CharacteristicCategory::with(array(
                'parent', 
                'characteristics' => function($query) use ($foundCharacteristicIds)
                    {
                        $query->whereActive()->whereVisible()->whereIn('id', $foundCharacteristicIds)->orderBy('name', 'asc');
                    }, 
            ))
            ->whereHas('characteristics', function($query) use ($foundCharacteristicIds)
                {
                    $query->whereActive()->whereVisible()->whereIn('id', $foundCharacteristicIds);
                })
            ->select('characteristic_categories.*')
            ->join('characteristic_categories as parent', 'parent.id', '=', 'characteristic_categories.parent_category_id')
            ->whereNotHealth()
            ->orderBy('parent.name', 'asc')
            ->orderBy('characteristic_categories.name', 'asc')
            ->get();

        $characteristicCategories = [];

        foreach($categories as $category)
        {
            $characteristicCategories[] = array(
                'name'            => $category->name, 
                'parent_name'     => $category->parent->name, 
                'characteristics' => $category->characteristics, 
            );
        }

        $selectedCharacteristic = null;

        // Show the page
        return View::make('characteristics/_dropdown', compact('hidden', 'counter', 'characteristicCategories', 'selectedCharacteristic'));
    }

    public function postCustomImportProfiles()
    {
        $characteristicId = Input::get('characteristic');
        $breedId = Input::get('breed');
        $sexId   = Input::get('sex');

        // Find the sex
        $sex = Sex::find($sexId);

        if (is_null($sex))
        {
            throw new Dynasty\Sexes\Exceptions\NotFoundException;
        }

        // Find the breed
        $breed = Breed::whereActive()->whereImportable()->where('id', $breedId)->first();

        if (is_null($breed))
        {
            throw new Dynasty\Breeds\Exceptions\NotFoundException;
        }

        // Find the characteristic
        $breedCharacteristic = $breed->characteristics()
            ->whereHas('characteristic', function($query)
                {
                    $query->whereActive()->whereVisible()->whereNotHealth();
                })
            ->whereVisible()
            ->where('characteristic_id', $characteristicId)
            ->first();

        if (is_null($breedCharacteristic))
        {
            throw new Dynasty\BreedCharacteristics\Exceptions\NotFoundException;
        }

        $characteristic = $breedCharacteristic->characteristic;
        $counter = ':counter_replace';

        // Get the phenotypes
        $phenotypes = $breedCharacteristic->queryPhenotypes()->orderBy('name', 'asc')->get();

        // Get the genotypes in the breed
        $breedGenotypeIds = $breed->genotypes()->wherePivot('frequency', '>', 0)->lists('id');

        // Always add -1
        $breedGenotypeIds[] = -1;

        // Get the loci
        $loci = $characteristic->loci()->with(array(
                'genotypes' => function($query) use ($breedGenotypeIds)
                    {
                        $query->whereIn('genotypes.id', $breedGenotypeIds)->orderByAlleles();
                    }
            ))
            ->whereHas('genotypes', function($query) use ($breedGenotypeIds)
                {
                    $query->whereIn('genotypes.id', $breedGenotypeIds);
                })
            ->whereActive()
            ->orderBy('name', 'asc')
            ->get();

        // Get the range
        $range = [];

        if ($sex->isFemale())
        {
            $range['min_ranged_value'] = ceil($breedCharacteristic->min_female_ranged_value);
            $range['max_ranged_value'] = floor($breedCharacteristic->max_female_ranged_value);
        }
        else
        {
            $range['min_ranged_value'] = ceil($breedCharacteristic->min_male_ranged_value);
            $range['max_ranged_value'] = floor($breedCharacteristic->max_male_ranged_value);
        }

        $range['lower_boundary_label'] = $characteristic->formatRangedValue($range['min_ranged_value']);
        $range['upper_boundary_label'] = $characteristic->formatRangedValue($range['max_ranged_value']);

        // Show the page
        return View::make('characteristics/_custom_import_profiles', compact(
            'breedCharacteristic', 'breed', 'characteristic', 'sex', 'counter', 
            'loci', 'phenotypes', 'range'
        ));
    }

}
