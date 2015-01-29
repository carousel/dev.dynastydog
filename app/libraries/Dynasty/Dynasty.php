<?php  

namespace Dynasty;

use Str;

class Dynasty {

    public static function credits($amount)
    {
        return number_format($amount).' '.Str::plural('Credit', $amount);
    }
    
    public static function turns($amount)
    {
        return number_format($amount).' '.Str::plural('Turn', $amount);
    }

    public static function referralPoints($amount)
    {
        return number_format($amount).' Referral '.Str::plural('Point', $amount);
    }

    public static function imports($amount)
    {
        return number_format($amount).' '.Str::plural('Import', $amount);
    }

    public static function customImports($amount)
    {
        return number_format($amount).' Custom '.Str::plural('Import', $amount);
    }

}
