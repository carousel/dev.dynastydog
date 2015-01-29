<?php namespace App\Validation;
 
use Cviebrock\ImageValidator\ImageValidator;
use AlphaCode;

class CustomValidator extends ImageValidator {

    public function validateAlphaSpaces($attribute, $value)
    {
        return preg_match('/^[\pL\s]+$/u', $value);
    }
 
    public function validateImageUrl($attribute, $value, $parameters)
    {
        
        $imageSize = @getimagesize($value);

        if ($imageSize === FALSE || ! is_array($imageSize))
            return FALSE;

        $mimes = $parameters;

        // No mimes to check against
        if (empty($mimes))
        {
            // Add in the basics
            $mimes = [ 'jpeg', 'png', 'gif', 'bmp' ];
        }

        $ext = image_type_to_extension($imageSize[2], FALSE);

        return in_array($ext, $mimes);
    }

    /**
     * Usage: image_url_size:width[,height]
     *
     * @param  $attribute  string
     * @param  $value      string|array
     * @param  $parameters array
     * @return boolean
     */
    public function validateImageUrlSize($attribute, $value, $parameters)
    {
        $imageSize = @getimagesize($value);

        if ($imageSize === FALSE || ! is_array($imageSize))
            return FALSE;


        // If only one dimension rule is passed, assume it applies to both height and width.
        if ( !isset($parameters[1]) )
        {
            $parameters[1] = $parameters[0];
        }

        // Parse the parameters.  Options are:
        //
        //  "300" or "=300"   - dimension must be exactly 300 pixels
        //  "<300"            - dimension must be less than 300 pixels
        //  "<=300"           - dimension must be less than or equal to 300 pixels
        //  ">300"            - dimension must be greater than 300 pixels
        //  ">=300"           - dimension must be greater than or equal to 300 pixels

        $width_check  = $this->checkDimension($parameters[0], $imageSize[0]);
        $height_check = $this->checkDimension($parameters[1], $imageSize[1]);

        return $width_check['pass'] && $height_check['pass'];
    }

    /**
     * Usage: imageAspect:ratio
     *
     * @param  $attribute  string
     * @param  $value      string|array
     * @param  $parameters array
     * @return boolean
     */
    public function validateImageUrlAspect($attribute, $value, $parameters)
    {
        $imageSize = @getimagesize($value);

        if ($imageSize === false || ! is_array($imageSize)) 
            return false;

        $imageAspect = bcdiv($imageSize[0], $imageSize[1], 12);

        // Parse the parameter(s).  Options are:
        //
        //  "0.75"   - one param: a decimal ratio (width/height)
        //  "3,4"    - two params: width, height
        //
        // If the first value is prefixed with "~", the orientation doesn't matter, i.e.:
        //
        //  "~3,4"   - would accept either "3:4" or "4:3" images

        $both_orientations = false;

        if (substr($parameters[0],0,1) == '~')
        {
            $parameters[0] = substr($parameters[0], 1);
            $both_orientations = true;
        }

        if (count($parameters) == 1)
        {
            $aspect = $parameters[0];
        }
        else
        {
            $width  = intval($parameters[0]);
            $height = intval($parameters[1]);

            if ($height==0 || $width==0)
            {
                throw new \RuntimeException('Aspect is zero or infinite: ' . $parameters[0] );
            }

            $aspect = bcdiv($width, $height, 12);
        }

        if ( bccomp($aspect, $imageAspect, 10) == 0 )
        {
            return true;
        }

        if ( $both_orientations )
        {
            $inverse = bcdiv(1, $aspect, 12);

            if ( bccomp($inverse, $imageAspect, 10)==0 )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Provides 'hexcolor' validation rule for Laravel4
     *
     * @return bool
     */
    public function validateHexcolor($attribute, $value, $parameters)
    {
        $pattern = '/^#?[a-fA-F0-9]{3,6}$/';
        return (boolean) preg_match($pattern, $value);
    }
    
    protected function replaceImageUrl($message, $attribute, $rule, $parameters)
    {
        return str_replace(':values', implode(', ', $parameters), $message);
    }

    /**
     * Build the error message for validation failures.
     *
     * @param  string $message
     * @param  string $attribute
     * @param  string $rule
     * @param  array $parameters
     * @return string
     */
    public function replaceImageUrlSize($message, $attribute, $rule, $parameters)
    {
        return $this->replaceImageSize($message, $attribute, $rule, $parameters);
    }

    /**
     * Build the error message for validation failures.
     *
     * @param  string $message
     * @param  string $attribute
     * @param  string $rule
     * @param  array $parameters
     * @return string
     */
    public function replaceImageUrlAspect($message, $attribute, $rule, $parameters)
    {
        return $this->replaceImageAspect($message, $attribute, $rule, $parameters);
    }

}