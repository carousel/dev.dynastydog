<?php namespace Controllers\BreedRegistry;

use AuthorizedController;
use App;
use Redirect;
use View;
use Input;
use Validator;
use Lang;
use DB;
use URL;
use Breed;
use Characteristic;

class BreedController extends AuthorizedController {

    public function getIndex($breed)
    {
        if ( ! $breed->isActive())
        {
            App::abort('404', 'Breed not found!');
        }

        $totalAliveDogs = $breed->dogs()->whereAlive()->count();
        $totalActiveBreedMembers = $breed->dogs()->whereActiveBreedMember()->count();

        $breedCharacteristics = $breed->characteristics()
            ->with('characteristic')
            ->whereHas('characteristic', function($query)
                {
                    $query->whereActive()->whereVisible()->whereNotHealth();
                })
            ->whereActive()
            ->whereVisible()
            ->orderByCharacteristic()
            ->get();

        $totalAffectedDogs = 0;
        $healthStatistics = [];

        if (Input::get('view') == 'health' and $totalAliveDogs > 0)
        {
            $disorders = Characteristic::with('genotypes')
                ->whereActive()->whereVisible()->whereHealth()
                ->where('type_id', Characteristic::TYPE_NORMAL)
                ->orderBy('name', 'asc')
                ->get();

            foreach($disorders as $disorder)
            {
                // Get all of the possible genotypes
                $genotypes = $disorder->genotypes;

                $genotypeIdsByLocusId = [];

                foreach($genotypes as $genotype)
                {
                    $genotypeIdsByLocusId[$genotype->locus_id][] = $genotype->id;
                };

                // Grab just the genotype IDs
                $genotypeIds = array_flatten($genotypeIdsByLocusId);

                // Count the loci
                $totalLoci = count($genotypeIdsByLocusId);

                // Find all dogs that have at least one genotype at each locus
                $foundDogs = DB::table('dog_genotypes')
                    ->select('dog_genotypes.dog_id', DB::raw('COUNT(DISTINCT genotypes.locus_id) as counted'))
                    ->join('dogs', 'dogs.id', '=', 'dog_genotypes.dog_id')
                    ->join('genotypes', 'genotypes.id', '=', 'dog_genotypes.genotype_id')
                    ->whereNotNull('dogs.owner_id')
                    ->whereNull('dogs.deceased_at')
                    ->where('dogs.breed_id', '=', $breed->id)
                    ->whereIn('dog_genotypes.genotype_id', $genotypeIds)
                    ->having('counted', '=', $totalLoci)
                    ->groupBy('dog_genotypes.dog_id')
                    ->get();

                $totalDogs = count($foundDogs);

                if ($totalDogs > 0)
                {
                    $totalAffectedDogs += $totalDogs;

                    $healthStatistics[] = array(
                        'characteristic' => $disorder, 
                        'total_dogs'     => $totalDogs, 
                    );
                }
            }

            // Sort by most affected then name
            usort($healthStatistics, function($a, $b)
                {
                    $totalA = $a['total_dogs'];
                    $totalB = $b['total_dogs'];

                    return ($totalA == $totalB)
                        ? ($a['characteristic']->name > $b['characteristic']->name) ? 1 : -1
                        : ($totalA > $totalB) ? -1 : 1;
                }
            );
        }

        // Show the page
        return View::make('frontend/breed_registry/breed/index', compact(
            'totalAliveDogs', 'totalActiveBreedMembers', 'breedCharacteristics', 
            'healthStatistics', 'totalAffectedDogs', 
            'breed'
        ));
    }

    public function getCharacteristic($breedCharacteristic)
    {
        // Grab the characteristic
        $characteristic = $breedCharacteristic->characteristic;

        // Make sure the characteristic can be viewed
        if ( ! $breedCharacteristic->isActive() or $breedCharacteristic->isHidden() or $breedCharacteristic->isHealth() or ! $characteristic->isActive() or $characteristic->isHidden())
        {
            App::abort('404', 'Characteristic not found!');
        }

        // Show the page
        return View::make('frontend/breed_registry/breed/characteristic', compact('breedCharacteristic'));
    }

}
