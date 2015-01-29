<?php  

namespace Floats;

class Floats {

    public static function compare($float1, $float2, $operator = '=')  
    {
        // Check numbers to 5 digits of precision  
        $epsilon = 0.00001;  
          
        $float1 = (float)$float1;  
        $float2 = (float)$float2;  
          
        switch ($operator)  
        {  
            // equal  
            case "===" :
            case "==" :
            case "="  :
            case "eq" :
                if (abs($float1 - $float2) < $epsilon)
                {  
                    return true;  
                }  

                break;

            // less than  
            case "<"  : 
            case "lt" : 
                if (abs($float1 - $float2) < $epsilon)
                {
                    return false;
                }
                else if ($float1 < $float2)
                {
                    return true;
                }

                break;

            // less than or equal
            case "<="  : 
            case "lte" : 
                if (self::compare($float1, $float2, '<') || self::compare($float1, $float2, '='))
                {
                    return true;
                }

                break;

            // greater than
            case ">"  : 
            case "gt" : 
                if (abs($float1 - $float2) < $epsilon)
                {
                    return false;
                }
                else if ($float1 > $float2)
                {
                    return true;
                }

                break;

            // greater than or equal
            case ">="  : 
            case "gte" : 
                if (self::compare($float1, $float2, '>') || self::compare($float1, $float2, '='))
                {
                    return true;
                }

                break;

            case "<>" : 
            case "!=" : 
            case "ne" : 
                if (abs($float1 - $float2) > $epsilon)
                {
                    return true;
                }

                break;

            default:
                break;
        }
        
        return false;
    }

    public static function mtRand($val1, $val2, $decimalPlaces = 2)
    {
        $min = min($val1, $val2);
        $max = max($val1, $val2);

        $precision = 1;

        for($i = 0; $i < $decimalPlaces; ++$i)
        {
            $precision *= 10;
        }

        return mt_rand($min * $precision, $max * $precision) / $precision;
    }
    
    public static function standardValue($val1, $val2)
    {
        $lowest  = min($val1, $val2) * 0.9;
        $highest = max($val1, $val2) * 1.1;

        $avg    = ($highest + $lowest) / 2;
        $stddev = ($highest - $lowest) / 8;

        $chance = mt_rand(1, 100);

        if ($chance <= 1)
        {
            if (mt_rand(1, 2) === 1) // < 0
            {
                $lb = $avg - (4 * $stddev);
                $ub = $avg - (3 * $stddev);
            }
            else
            {
                $lb = $avg + (3 * $stddev);
                $ub = $avg + (4 * $stddev);
            }
        }
        else if ($chance <= 4)
        {
            if (mt_rand(1, 2) === 1) // < 0
            {
                $lb = $avg - (3 * $stddev);
                $ub = $avg - (2 * $stddev);
            }
            else
            {
                $lb = $avg + (2 * $stddev);
                $ub = $avg + (3 * $stddev);
            }
        }
        else if ($chance <= 27)
        {
            if (mt_rand(1, 2) === 1) // < 0
            {
                $lb = $avg - (2 * $stddev);
                $ub = $avg - (1 * $stddev);
            }
            else
            {
                $lb = $avg + (1 * $stddev);
                $ub = $avg + (2 * $stddev);
            }
        }
        else
        {
            $lb = $avg - $stddev;
            $ub = $avg + $stddev;
        }

        return [ $lb, $ub ];
    }

    public static function normalizeValueInRange($value, $oldRange, $newRange)
    {
        $divisor = $oldRange[1] - $oldRange[0];
        
        $a = ($divisor == 0) ? $divisor : ($newRange[1] - $newRange[0]) / $divisor;

        $b = $newRange[1] - ($a * $oldRange[1]);

        return ($a * $value) + $b;
    }

}
