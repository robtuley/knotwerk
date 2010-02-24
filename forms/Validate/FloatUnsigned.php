<?php
/**
 * Defines the T_Validate_FloatUnsigned class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Validate an unsigned (i.e. positive) float.
 *
 * @package forms
 */
class T_Validate_FloatUnsigned extends T_Filter_Skeleton
{

    /**
     * Validate an unsigned float input.
     *
     * @param float $value  float value
     * @return float  the value
     */
    protected function doTransform($value)
    {
        $regex = '/^(?:\.\d+|\d+\.?\d*)$/';
          /* either
           *   (a) Starts with a dot (.1234) == 0.1234
           *   (b) integer digits only, followed by an optional 
           *       dot with possibly some digits after
           */ 
        if (!preg_match($regex,(string) $value)) {
            throw new T_Exception_Filter('must be a number greater than zero');
        }
        return (float) $value;
    }

}