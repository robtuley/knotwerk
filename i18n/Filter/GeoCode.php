<?php
/**
 * Defines the T_Filter_GeoCode interface.
 *
 * @package geo
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A filter that geocodes a string.
 *
 * @package geo
 */
interface T_Filter_GeoCode extends T_Filter
{
    
    /**
     * Bias the geocode to a particular country.
     *
     * @param string $code  ISO 3166-1 country code
     * @return T_Google_GeoCode  fluent interface
     */
    function biasToCountry($code);
    
}