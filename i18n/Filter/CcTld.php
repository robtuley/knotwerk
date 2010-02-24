<?php
/**
 * Defines the T_Filter_CcTld class.
 *
 * @package geo
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Converts an ISO 3166-1 two letter code to a top-level country code domain.
 *
 * ISO 3166-1 country codes are generally the same as the top level country
 * code domain, with the exception of the United Kingdom where 'GB' is the
 * country code, but 'uk' is the top level country domain (.co.uk).
 *
 * @package geo
 */
class T_Filter_CcTld extends T_Filter_Skeleton
{

    /**
     * Converts ISO 3166-1 code to TLD country code.
     *
     * @param mixed $value  ISO 3166-1 code
     * @return string  TLD country code
     */
    protected function doTransform($value)
    {
        $value = strtolower($value);
        if (strcmp($value,'gb')===0) $value = 'uk';
        return $value;
    }

}