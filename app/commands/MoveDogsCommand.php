<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MoveDogsCommand extends Command {

    const NEW_DB = 'mysql';
    const OLD_DB = 'old_mysql';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'movehouse:dogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move all old dogs from the production database to the laravel database';

    protected $oldDB;
    protected $newDB;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->oldDB = DB::connection(self::OLD_DB);
        $this->newDB = DB::connection(self::NEW_DB);

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('Fired up');

        $timeStart = microtime(true);

        $this->newDB->transaction(function()
        {
            $this->newDB->statement('SET FOREIGN_KEY_CHECKS=0;');

            // $this->newDB->table('dogs')->truncate();
            // $this->newDB->table('dog_characteristics')->truncate();
            // $this->newDB->table('dog_characteristic_genotypes')->truncate();
            // $this->newDB->table('dog_characteristic_phenotypes')->truncate();
            // $this->newDB->table('dog_characteristic_symptoms')->truncate();
            // $this->newDB->table('dog_characteristic_tests')->truncate();
            // $this->newDB->table('dog_genotypes')->truncate();
            // $this->newDB->table('dog_phenotypes')->truncate();
            // $this->newDB->table('pedigrees')->truncate();

            for($i = 0; $i < 100; ++$i)
            {
                $this->info('Start of iteration '.($i + 1));

                // Get the last dog ID done
                $lastMovedDogId = (int) $this->newDB->table('dogs')->max('id');

                $dog = $this->oldDB->table('dogs')->where('id', '>', $lastMovedDogId)->first();

                if (is_null($dog))
                {
                    $this->info('No dogs moved!');
                }
                else
                {
                    $this->info('Started moving dog #'.$dog->id);

                    $this->moveDog($dog);
                    $this->moveDogCharacteristics($dog);
                    $this->moveDogCharacteristicGenetics($dog);
                    $this->moveDogCharacteristicGeneticGenotypes($dog);
                    $this->moveDogCharacteristicGeneticPhenotypes($dog);
                    $this->moveDogCharacteristicHealth($dog);
                    $this->moveDogCharacteristicHealthSeverities($dog);
                    $this->moveDogCharacteristicHealthSeveritiesHealthSymptoms($dog);
                    $this->moveDogCharacteristicRanges($dog);
                    $this->moveDogCharacteristicTests($dog);
                    $this->moveDogGenotypes($dog);
                    $this->moveDogPedigree($dog);
                    $this->moveDogPhenotypes($dog);

                    $this->info('Ended moving dog #'.$dog->id);
                }

                $this->info('End of iteration '.($i + 1));
            }

            $this->newDB->statement('SET FOREIGN_KEY_CHECKS=1;');
        });

        $timeEnd = microtime(true);
        $this->info('Script took '.($timeEnd - $timeStart).' seconds ('.(($timeEnd - $timeStart) / 60).' minutes)');
    }

    public function moveDog($record)
    {
        $values = array(
            'id' => $record->id, 
            'owner_id' => $record->user_id, 
            'breeder_id' => $record->breeder_id, 
            'kennel_prefix' => $record->kennel_prefix, 
            'kennel_group_id' => $record->kennel_group_id, 
            'name' => $record->name, 
            'display_image' => $record->display_image, 
            'image_url' => $record->image_url, 
            'notes' => $record->notes, 
            'studding' => $record->studding, 
            'breed_id' => $record->breed_id, 
            'breed_changed' => $record->breed_changed, 
            'litter_id' => $record->litter_id, 
            'sex_id' => ($record->sex_id + 1), 
            'age' => $record->age, 
            'coi' => $record->coi, 
            'active_breed_member' => $record->active_breed_member, 
            'worked' => $record->worked, 
            'heat' => $record->heat, 
            'small_contest_wins' => $record->small_contest_wins, 
            'medium_contest_wins' => $record->medium_contest_wins, 
            'large_contest_wins' => $record->large_contest_wins, 
            'custom_import' => $record->custom_import, 
            'created_at' => $this->formatDateTime($record->created), 
            'updated_at' => $this->formatDateTime($record->created), 
            'completed_at' => ($record->completed ? $this->formatDateTime($record->completed) : null), 
            'deceased_at' => ($record->deceased ? $this->formatDateTime($record->deceased) : null), 
            );

        if ( ! empty($values))
        {
            $this->newDB->table('dogs')->insert($values);
        }

        $this->info('Moved table:dogs');
    }

    public function moveDogCharacteristics($oldDog)
    {
        $records = $this->oldDB->table('dog_characteristics')->where('dog_id', $oldDog->id)->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'dog_id' => $record->dog_id, 
                'in_summary' => $record->in_summary, 
                'hide' => $record->hidden, 
                'characteristic_id' => $record->characteristic_id, 
                'age_to_reveal_genotypes' => null, 
                'genotypes_revealed' => null, 
                'age_to_reveal_phenotypes' => null, 
                'phenotypes_revealed' => null, 
                'final_ranged_value' => null, 
                'age_to_stop_growing' => null, 
                'current_ranged_value' => null, 
                'age_to_reveal_ranged_value' => null, 
                'ranged_value_revealed' => null, 
                'characteristic_severity_id' => null, 
                'age_to_express_severity' => null, 
                'severity_expressed' => null, 
                'severity_value' => null, 
                'age_to_reveal_severity_value' => null, 
                'severity_value_revealed' => null, 
                'last_tested_at_months' => null, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('dog_characteristics')->insert($values);
        }

        $this->info('Moved table:dog_characteristics '.count($values));
    }

    public function moveDogCharacteristicGenetics($oldDog)
    {
        $records = $this->oldDB->table('dog_characteristic_genetics')
            ->select('dog_characteristic_genetics.*', 'characteristic_genetics.phenotypes_can_be_known', 'characteristic_genetics.genotypes_can_be_known')
            ->join('dog_characteristics', 'dog_characteristics.id', '=', 'dog_characteristic_genetics.dog_characteristic_id')
            ->join('characteristic_genetics', 'characteristic_genetics.id', '=', 'dog_characteristic_genetics.characteristic_genetic_id')
            ->where('dog_characteristics.dog_id', '=', $oldDog->id)
            ->get();

        $count = 0;

        foreach($records as $record)
        {
            $this->newDB->table('dog_characteristics')
                ->where('id', $record->dog_characteristic_id)
                ->update(array(
                    'age_to_reveal_genotypes' => ($record->genotypes_can_be_known ? $record->genotypes_can_be_known_age : null), 
                    'genotypes_revealed' => ($record->genotypes_can_be_known ? true : false), 
                    'age_to_reveal_phenotypes' => ($record->phenotypes_can_be_known ? $record->phenotypes_can_be_known_age : null), 
                    'phenotypes_revealed' => ($record->phenotypes_can_be_known ? true : false), 
                ));

            ++$count;
        }

        $this->info('Moved table:dog_characteristic_genetics '.$count);
    }

    public function moveDogCharacteristicGeneticGenotypes($oldDog)
    {
        $records = $this->oldDB->table('dog_characteristic_genetic_genotypes')
            ->select('dog_characteristic_genetic_genotypes.known', 'dog_characteristic_genetics.dog_characteristic_id', 'dog_genotypes.genotype_id')
            ->join('dog_characteristic_genetics', 'dog_characteristic_genetics.id', '=', 'dog_characteristic_genetic_genotypes.dog_characteristic_genetic_id')
            ->join('dog_characteristics', 'dog_characteristics.id', '=', 'dog_characteristic_genetics.dog_characteristic_id')
            ->join('dog_genotypes', 'dog_genotypes.id', '=', 'dog_characteristic_genetic_genotypes.dog_genotype_id')
            ->where('dog_characteristics.dog_id', '=', $oldDog->id)
            ->get();

        $values = [];

        $saveUniques = [];

        foreach($records as $record)
        {
            $this->newDB->table('dog_characteristics')
                ->where('id', $record->dog_characteristic_id)
                ->update(array(
                    'genotypes_revealed' => ($record->known ? true : false), 
                ));

            if ( ! array_key_exists($record->dog_characteristic_id, $saveUniques) or ! in_array($record->genotype_id, $saveUniques[$record->dog_characteristic_id]))
            {
                $values[] = array(
                    'dog_characteristic_id' => $record->dog_characteristic_id, 
                    'genotype_id' => $record->genotype_id,
                );

                $saveUniques[$record->dog_characteristic_id][] = $record->genotype_id;
            }
        }

        if ( ! empty($values))
        {
            $this->newDB->table('dog_characteristic_genotypes')->insert($values);
        }

        $this->info('Moved table:dog_characteristic_genetic_genotypes '.count($values));
    }

    public function moveDogCharacteristicGeneticPhenotypes($oldDog)
    {
        $records = $this->oldDB->table('dog_characteristic_genetic_phenotypes')
            ->select('dog_characteristic_genetic_phenotypes.known', 'dog_characteristic_genetics.dog_characteristic_id', 'dog_phenotypes.phenotype_id')
            ->join('dog_characteristic_genetics', 'dog_characteristic_genetics.id', '=', 'dog_characteristic_genetic_phenotypes.dog_characteristic_genetic_id')
            ->join('dog_characteristics', 'dog_characteristics.id', '=', 'dog_characteristic_genetics.dog_characteristic_id')
            ->join('dog_phenotypes', 'dog_phenotypes.id', '=', 'dog_characteristic_genetic_phenotypes.dog_phenotype_id')
            ->where('dog_characteristics.dog_id', '=', $oldDog->id)
            ->get();

        $values = [];

        $saveUniques = [];

        foreach($records as $record)
        {
            $this->newDB->table('dog_characteristics')
                ->where('id', $record->dog_characteristic_id)
                ->update(array(
                    'phenotypes_revealed' => ($record->known ? true : false), 
                ));


            if ( ! array_key_exists($record->dog_characteristic_id, $saveUniques) or ! in_array($record->phenotype_id, $saveUniques[$record->dog_characteristic_id]))
            {
                $values[] = array(
                    'dog_characteristic_id' => $record->dog_characteristic_id, 
                    'phenotype_id' => $record->phenotype_id,
                );

                $saveUniques[$record->dog_characteristic_id][] = $record->phenotype_id;
            }
        }

        if ( ! empty($values))
        {
            $this->newDB->table('dog_characteristic_phenotypes')->insert($values);
        }

        $this->info('Moved table:dog_characteristic_genetic_phenotypes '.count($values));
    }

    public function moveDogCharacteristicHealth($oldDog)
    {
        // 
    }

    public function moveDogCharacteristicHealthSeverities($oldDog)
    {
        $records = $this->oldDB->table('dog_characteristic_health_severities')
            ->select('dog_characteristic_health_severities.*', 'dog_characteristic_health.dog_characteristic_id', 'characteristic_health_severities.value_can_be_known')
            ->join('dog_characteristic_health', 'dog_characteristic_health.id', '=', 'dog_characteristic_health_severities.dog_characteristic_health_id')
            ->join('dog_characteristics', 'dog_characteristics.id', '=', 'dog_characteristic_health.dog_characteristic_id')
            ->join('characteristic_health_severities', 'characteristic_health_severities.id', '=', 'dog_characteristic_health_severities.characteristic_health_severity_id')
            ->where('dog_characteristics.dog_id', '=', $oldDog->id)
            ->get();

        $count = 0;

        foreach($records as $record)
        {
            $this->newDB->table('dog_characteristics')
                ->where('id', $record->dog_characteristic_id)
                ->update(array(
                    'characteristic_severity_id' => $record->characteristic_health_severity_id, 
                    'severity_value' => $record->value, 
                    'age_to_express_severity' => (true ? $record->onset_age : null), 
                    'severity_expressed' => (true ? true : false), 
                    'age_to_reveal_severity_value' => ($record->value_can_be_known ? $record->value_can_be_known_age : null), 
                    'severity_value_revealed' => ($record->value_is_known ? true : false), 
                ));

            ++$count;
        }

        $this->info('Moved table:dog_characteristic_health_severities '.$count);
    }

    public function moveDogCharacteristicHealthSeveritiesHealthSymptoms($oldDog)
    {
        $records = $this->oldDB->table('dog_characteristic_health_severity_health_symptoms')
            ->select('dog_characteristic_health_severity_health_symptoms.*', 'dog_characteristic_health.dog_characteristic_id', DB::raw('(dog_characteristic_health_severity_health_symptoms.offset_onset_age + dog_characteristic_health_severities.onset_age) as age_to_express'))
            ->join('dog_characteristic_health_severities', 'dog_characteristic_health_severities.id', '=', 'dog_characteristic_health_severity_health_symptoms.dog_characteristic_health_severity_id')
            ->join('dog_characteristic_health', 'dog_characteristic_health.id', '=', 'dog_characteristic_health_severities.dog_characteristic_health_id')
            ->join('dog_characteristics', 'dog_characteristics.id', '=', 'dog_characteristic_health.dog_characteristic_id')
            ->where('dog_characteristics.dog_id', $oldDog->id)
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'id' => $record->id, 
                'dog_characteristic_id' => $record->dog_characteristic_id, 
                'characteristic_severity_symptom_id' => $record->characteristic_health_severity_health_symptom_id, 
                'age_to_express' => $record->age_to_express, 
                'expressed' => $record->is_active, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('dog_characteristic_symptoms')->insert($values);
        }

        $this->info('Moved table:characteristic_health_severity_health_symptoms '.count($values));
    }

    public function moveDogCharacteristicRanges($oldDog)
    {
        $records = $this->oldDB->table('dog_characteristic_ranges')
            ->select('dog_characteristic_ranges.*', 'characteristic_ranges.growth', 'characteristic_ranges.value_can_be_known')
            ->join('dog_characteristics', 'dog_characteristics.id', '=', 'dog_characteristic_ranges.dog_characteristic_id')
            ->join('characteristic_ranges', 'characteristic_ranges.id', '=', 'dog_characteristic_ranges.characteristic_range_id')
            ->where('dog_characteristics.dog_id', '=', $oldDog->id)
            ->get();

        $count = 0;

        foreach($records as $record)
        {
            $this->newDB->table('dog_characteristics')
                ->where('id', $record->dog_characteristic_id)
                ->update(array(
                    'final_ranged_value' => $record->grow_until, 
                    'age_to_stop_growing' => ($record->growth ? $record->growth_age : null), 
                    'current_ranged_value' => $record->value, 
                    'age_to_reveal_ranged_value' => ($record->value_can_be_known ? $record->value_can_be_known_age : null), 
                    'ranged_value_revealed' => ($record->value_is_known ? true : false), 
                ));

            ++$count;
        }

        $this->info('Moved table:dog_characteristic_ranges '.$count);
    }

    public function moveDogCharacteristicTests($oldDog)
    {
        $records = $this->oldDB->table('dog_characteristic_tests')
            ->select('dog_characteristic_tests.*', 'characteristic_tests.characteristic_id')
            ->join('characteristic_tests', 'characteristic_tests.id', '=', 'dog_characteristic_tests.characteristic_test_id')
            ->where('dog_characteristic_tests.dog_id', '=', $oldDog->id)
            ->get();

        $values = [];

        foreach($records as $record)
        {
            // Grab the dog characteristic
            $dogCharacteristic = $this->oldDB->table('dog_characteristics')
                ->where('dog_id', '=', $oldDog->id)
                ->where('characteristic_id', '=', $record->characteristic_id)
                ->first();

            $values[] = array(
                'dog_characteristic_id' => $dogCharacteristic->id, 
                'test_id' => $record->characteristic_test_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('dog_characteristic_tests')->insert($values);
        }

        $this->info('Moved table:dog_characteristic_tests '.count($values));
    }

    public function moveDogGenotypes($oldDog)
    {
        $records = $this->oldDB->table('dog_genotypes')->where('dog_id', $oldDog->id)->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'dog_id' => $record->dog_id, 
                'genotype_id' => $record->genotype_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('dog_genotypes')->insert($values);
        }

        $this->info('Moved table:dog_genotypes '.count($values));
    }

    public function moveDogPedigree($oldDog)
    {
        $records = $this->oldDB->table('dog_pedigrees')
            ->where('dog_pedigrees.dog_id', '=', $oldDog->id)
            ->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'dog_id' => $record->dog_id, 
                'dam' => $record->dam, 
                'sire' => $record->sire, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('pedigrees')->insert($values);
        }

        $this->info('Moved table:dog_pedigrees '.count($values));
    }

    public function moveDogPhenotypes($oldDog)
    {
        $records = $this->oldDB->table('dog_phenotypes')->where('dog_id', $oldDog->id)->get();

        $values = [];

        foreach($records as $record)
        {
            $values[] = array(
                'dog_id' => $record->dog_id, 
                'phenotype_id' => $record->phenotype_id, 
            );
        }

        if ( ! empty($values))
        {
            $this->newDB->table('dog_phenotypes')->insert($values);
        }

        $this->info('Moved table:dog_phenotypes '.count($values));
    }

    public function formatDateTime($string)
    {
        return date('Y-m-d H:i:s', $string);
    }

    public function formatDate($string)
    {
        return date('Y-m-d', $string);
    }

}
