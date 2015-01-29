<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CheckDogMaturitiesCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'dogs:maturities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the maturities of dogs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
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

        DB::transaction(function()
        {
            DB::table('dogs')
                ->update(array(
                    'sexually_mature' => false, 
                    'sexual_decline' => false, 
                    'infertile' => false, 
                ));

            // Check sexual maturity
            $sexuallyMatureDogIds = DB::table('dogs')
                ->select('dogs.id')
                ->join('dog_characteristics', 'dog_characteristics.dog_id', '=', 'dogs.id')
                ->join('characteristics', 'characteristics.id', '=', 'dog_characteristics.characteristic_id')
                ->whereNotNull('dogs.completed_at')
                ->where('characteristics.type_id', Characteristic::TYPE_SEXUAL_MATURITY)
                ->whereRaw('dogs.age >= IF(dog_characteristics.id IS NULL, 0, ROUND(dog_characteristics.current_ranged_value))')
                ->lists('dogs.id');

            // Always add -1
            $sexuallyMatureDogIds[] = -1;

            DB::table('dogs')
                ->whereIn('id', $sexuallyMatureDogIds)
                ->update(array(
                    'sexually_mature' => true, 
                ));

            // Check sexual decline
            $sexuallyDecliningDogIds = DB::table('dogs')
                ->select(
                    'dogs.id',
                    'dogs.age as dogage',
                    DB::raw('IF(dogfspan.id IS NULL, 0, dogfspan.current_ranged_value) as dogfspanvalue'),
                    DB::raw('IF(doglspan.id IS NULL, 0, doglspan.current_ranged_value) as doglspanvalue'),
                    DB::raw('dogfdo.id as dogfdoid'),
                    DB::raw('IF(dogfdo.id IS NULL, 0, dogfdo.current_ranged_value) as dogfdovalue')
                )
                ->join('dog_characteristics as dogfdo', 'dogfdo.dog_id', '=', 'dogs.id')
                ->join('dog_characteristics as dogfspan', 'dogfspan.dog_id', '=', 'dogs.id')
                ->join('dog_characteristics as doglspan', 'doglspan.dog_id', '=', 'dogs.id')
                ->join('characteristics as fspan', 'fspan.id', '=', 'dogfspan.characteristic_id')
                ->join('characteristics as fdo', 'fdo.id', '=', 'dogfdo.characteristic_id')
                ->join('characteristics as lspan', 'lspan.id', '=', 'doglspan.characteristic_id')
                ->whereNotNull('dogs.completed_at')
                ->where('fdo.type_id', '=', Characteristic::TYPE_FERTILITY_DROP_OFF)
                ->where('fspan.type_id', '=', Characteristic::TYPE_FERTILITY_SPAN)
                ->where('lspan.type_id', '=', Characteristic::TYPE_LIFE_SPAN)
                ->havingRaw('dogage >= IF(dogfdoid IS NULL, 0, ROUND(ROUND((dogfspanvalue / 100.00) * doglspanvalue) - ((dogfdovalue / 100.00) * ROUND((dogfspanvalue / 100.00) * doglspanvalue))))')
                ->lists('dogs.id');

            // Always add -1
            $sexuallyDecliningDogIds[] = -1;

            DB::table('dogs')
                ->whereIn('id', $sexuallyDecliningDogIds)
                ->update(array(
                    'sexual_decline' => true, 
                ));

            // Check infertility
            $infertileDogIds = DB::table('dogs')
                ->select(
                    'dogs.id',
                    'dogs.age as dogage',
                    DB::raw('IF(dogfspan.id IS NULL, 0, dogfspan.current_ranged_value) as dogfspanvalue'),
                    DB::raw('IF(doglspan.id IS NULL, 0, doglspan.current_ranged_value) as doglspanvalue')
                )
                ->join('dog_characteristics as dogfspan', 'dogfspan.dog_id', '=', 'dogs.id')
                ->join('dog_characteristics as doglspan', 'doglspan.dog_id', '=', 'dogs.id')
                ->join('characteristics as fspan', 'fspan.id', '=', 'dogfspan.characteristic_id')
                ->join('characteristics as lspan', 'lspan.id', '=', 'doglspan.characteristic_id')
                ->whereNotNull('dogs.completed_at')
                ->where('fspan.type_id', '=', Characteristic::TYPE_FERTILITY_SPAN)
                ->where('lspan.type_id', '=', Characteristic::TYPE_LIFE_SPAN)
                ->havingRaw('dogage >= ROUND((dogfspanvalue / 100.00) * doglspanvalue)')
                ->lists('dogs.id');

            // Always add -1
            $infertileDogIds[] = -1;

            DB::table('dogs')
                ->whereIn('id', $infertileDogIds)
                ->update(array(
                    'infertile' => true, 
                ));
        });
    }

}
