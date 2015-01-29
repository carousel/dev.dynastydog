<?php

class Pedigree extends Eloquent {

    const MAX_HEIGHT = 10;

    const DAM      = 'd';
    const SIRE     = 's';
    const COI      = 'coi';
    const ANCESTOR = 'id';

    public $timestamps = false;

    protected $primaryKey = 'dog_id';

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    |
    |
    */

    public function setDamAttribute($dam)
    {
        $this->attributes['dam'] = json_encode($dam);
    }

    public function setSireAttribute($sire)
    {
        $this->attributes['sire'] = json_encode($sire);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    |
    |
    */

    public function getDamAttribute($dam)
    {
        return json_decode($dam, true);
    }

    public function getSireAttribute($sire)
    {
        return json_decode($sire, true);
    }

    /*
    |--------------------------------------------------------------------------
    | Belongs To Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Return the dog
     *
     * @return Dog
     */
    public function dog()
    {
        return $this->belongsTo('Dog', 'dog_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public static function node($id = null, $coi = 0.0)
    {
        return array(
            Pedigree::ANCESTOR => $id, 
            Pedigree::COI      => $coi,
        );
    }

    public static function imported()
    {
        // Create a blank slate of a pedigree
        $pedigree = new Pedigree;

        $pedigree->dam  = [ Pedigree::DAM => Pedigree::node() ];
        $pedigree->sire = [ Pedigree::SIRE => Pedigree::node() ];

        return $pedigree;
    }

    public static function bred($dam, $sire)
    {
        $damSide  = [ Pedigree::DAM  => Pedigree::node() ];
        $sireSide = [ Pedigree::SIRE => Pedigree::node() ];

        // Do the dam's side
        if ( ! is_null($dam))
        {
            $damSide[Pedigree::DAM] = Pedigree::node($dam->id, $dam->coi);

            // Grab the dam's pedigree
            $damPedigree = $dam->pedigree;

            if ( ! is_null($damPedigree))
            {
                $mergedDamPedigree = $damPedigree->merged();

                foreach($mergedDamPedigree as $depth => $node)
                {
                    $newDepth = Pedigree::DAM.$depth;

                    if (is_null(Pedigree::MAX_HEIGHT) or strlen($newDepth) <= Pedigree::MAX_HEIGHT)
                    {
                        $damSide[$newDepth]  = $node;
                    }
                }
            }
        }

        // Do the sire's side
        if ( ! is_null($sire))
        {
            $sireSide[Pedigree::SIRE]  = Pedigree::node($sire->id, $sire->coi);

            // Grab the sire's pedigree
            $sirePedigree = $sire->pedigree;

            if ( ! is_null($sirePedigree))
            {
                $mergedSirePedigree = $sirePedigree->merged();

                foreach($mergedSirePedigree as $depth => $node)
                {
                    $newDepth = Pedigree::SIRE.$depth;

                    if (is_null(Pedigree::MAX_HEIGHT) or strlen($newDepth) <= Pedigree::MAX_HEIGHT)
                    {
                        $sireSide[$newDepth]  = $node;
                    }
                }
            }
        }

        // Create a blank slate of a pedigree
        $pedigree = new Pedigree;

        $pedigree->dam  = $damSide;
        $pedigree->sire = $sireSide;

        return $pedigree;
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */

    public function merged()
    {
        return array_merge($this->dam, $this->sire);
    }

    public function calculateCoi()
    {
        $coi = 0.0;

        $dam  = $this->dam;
        $sire = $this->sire;

        if ( ! is_array($dam) OR ! is_array($sire)) // Something weird happened out of sync; this should never occur
        {
            return $coi;
        }

        $damAncestors = array_filter(array_map(array($this, 'pluckAncestors'), $dam));

        if (empty($damAncestors)) // Imported dog; won't ever have a significant COI
        {
            return $coi;
        }

        $sireAncestors = array_filter(array_map(array($this, 'pluckAncestors'), $sire));

        // ***********************************************************

        $common = array();

        $comparable = $sireAncestors;

        foreach ($damAncestors as $key => $ancestor)
        {
            $sireKey = array_search($ancestor, $comparable);

            if ($sireKey !== FALSE)
            {
                $length = strlen($sireKey);

                foreach ($comparable as $comparableKey => $comparable_ancestor)
                {
                    if ($comparableKey != $sireKey AND $sireKey == substr($comparableKey, 0, $length))
                    {
                        unset($comparable[$comparableKey]);
                    }
                }

                $common[$key] = $damAncestors[$key];
            }
        }

        // ***********************************************************

        // No common ancestors between the parents
        if (empty($common))
        {
            $parentsCoi = 0.00;

            if (array_key_exists(Pedigree::DAM, $dam))
            {
                $ancestor = $dam[Pedigree::DAM];
                $parentsCoi += $ancestor[Pedigree::COI];
            }

            if (array_key_exists(Pedigree::SIRE, $sire))
            {
                $ancestor = $sire[Pedigree::SIRE];
                $parentsCoi += $ancestor[Pedigree::COI];
            }

            // COI roughly degrades by half each generation
            $coi = $parentsCoi / 2.00;
        }
        // Parents have a common ancestor
        else
        {
            // Get paths to ancestor on dam's side
            $damPaths = array();

            // Get paths to ancestor on sire's side
            $sirePaths = array();

            $paths = array();

            foreach ($common as $key => $ancestor)
            {
                // Need to get all places this ancestor appears in the trees
                $damPaths  = $this->findPaths($ancestor, $damAncestors);
                $sirePaths = $this->findPaths($ancestor, $sireAncestors);

                // Get all combinations
                foreach ($damPaths[$ancestor] as $damAncestorPaths)
                {
                    array_pop($damAncestorPaths);

                    foreach ($sirePaths[$ancestor] as $sireAncestorPaths)
                    {
                        array_pop($sireAncestorPaths);

                        // $sireAncestorPaths = array_reverse($sireAncestorPaths); // Not needed for actual path computations

                        $path = array_merge($damAncestorPaths, $sireAncestorPaths);

                        $pathLength = count($path);

                        // Only affect coi if the path is unique (ie. not retraced at all)
                        if (count(array_unique($path)) == $pathLength)
                        {
                            $paths[$ancestor][] = $path;

                            $coi += pow((1/2), ($pathLength + 1)) * (1.0 + $dam[$key][Pedigree::COI]);
                        }
                    }
                }
            }
        }

        return $coi;
    }


    protected function pluckAncestors($val)
    {
        if (isset($val[Pedigree::ANCESTOR]))
        {
            return $val[Pedigree::ANCESTOR];
        }
    }

    protected function findPath($end, $pedigree)
    {
        $path = [];

        $length = strlen($end);

        for ($i = 1; $i <= $length; $i++)
        {
            $path[] = $pedigree[substr($end, 0, $i)];
        }

        return $path;
    }
     
    protected function findPaths($ancestor, $pedigree)
    {
        $paths = [];

        $endings = array_keys($pedigree, $ancestor);

        foreach ($endings as $end)
        {
            // Find all the ways I can make it back to start from end
            $path = $this->findPath($end, $pedigree);

            $paths[$ancestor][] = $path;
        }

        return $paths;
    }

    public function getHeight()
    {
        $damKeys = array_keys($this->dam);
        $maxDam  = max(array_map('strlen', $damKeys));

        $sireKeys = array_keys($this->sire);
        $maxSire  = max(array_map('strlen', $sireKeys));

        return ($maxDam > $maxSire) ? $maxDam : $maxSire;
    }

    public function displayData($maxDisplayLimit = Pedigree::MAX_HEIGHT)
    {
        $data    = [];
        $ordered = [];

        $displayLimit = min($maxDisplayLimit, $this->getHeight());

        $sire = $this->sire;
        $this->sortHalf($data, $displayLimit, $sire);
        $this->recursivelyOrder($ordered, $data, Pedigree::SIRE, $displayLimit);

        $dam = $this->dam;
        $this->sortHalf($data, $displayLimit, $dam);
        $this->recursivelyOrder($ordered, $data, Pedigree::DAM, $displayLimit);

        return $ordered;
    }

    protected function sortHalf(&$data, $displayLimit, $half)
    {
        foreach ($half as $key => $itemData)
        {
            $depth = strlen($key);

            if ($depth <= $displayLimit)
            {
                $data[$key] = $this->pedigreeItem($itemData, $key, $displayLimit);
            }
        }
    }

    protected function pedigreeItem($itemData, $key, $limit)
    {
        $ancestor = Dog::find($itemData[Pedigree::ANCESTOR]);
        $depth    = strlen($key);
        $rowspan  = (pow(2, $limit - $depth) * 2) - 1;

        return array(
            'ancestor' => $ancestor, 
            'type'     => substr($key, -1), 
            'rowspan'  => $rowspan, 
        );
    }

    protected function recursivelyOrder(&$ordered, $pedigree, $cur, $fill)
    {
        $depth = strlen($cur);

        if ($depth <= $fill)
        {
            if (isset($pedigree[$cur]))
            {
                $ordered[] = $pedigree[$cur];
            }
            else if ($depth <= $fill)
            {
                $ordered[] = $this->pedigreeItem(array('id' => null), $cur, $fill);
            }

            $this->recursivelyOrder($ordered, $pedigree, $cur.Pedigree::SIRE, $fill);
            $this->recursivelyOrder($ordered, $pedigree, $cur.Pedigree::DAM, $fill);
        }
    }

}
