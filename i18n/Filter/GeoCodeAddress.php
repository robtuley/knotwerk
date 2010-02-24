<?php
/**
 * Defines the T_Filter_GeoCodeAddress class.
 *
 * @package geo
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Geocodes an address.
 *
 * @package geo
 */
class T_Filter_GeoCodeAddress extends T_Filter_Skeleton
{

    /**
     * Geocoding driver.
     *
     * @var T_Filter_GeoCode
     */
    protected $driver;

    /**
     * Create address geocoding filter.
     *
     * @param T_Filter_GeoCode $driver  geocoding driver
     * @param T_Filter $prior  prior filter
     */
    function __construct(T_Filter_GeoCode $driver,$prior=null)
    {
        parent::__construct($prior);
        $this->driver = $driver;
    }

    /**
     * Locates an address.
     *
     * @param T_Geo_Address $addr
     * @return T_Geo_Address  point
     */
    protected function doTransform($addr)
    {
        // prepare country
        $addr = clone($addr);
        if ($ctry=$addr->getCountry()) {
            $this->driver->biasToCountry($ctry->getCode());
        } else {
            $this->driver->biasToCountry(false);
        }

        // build query string
        $text = array($addr->getLineOne(),
                          $addr->getLineTwo(),
                          $addr->getCity(),
                          $addr->getState(),
                          $addr->getPostcode());
        $text = implode(', ',array_filter($text));

        // attempt to geocode
        try {
            $point = _transform($text,$this->driver);
        } catch (T_Exception_Filter $e) {
            throw new T_Exception_Filter("Could not locate the address provided");
        }

        // populate data back into address
        $addr->setLongitude($point->getLongitude())
             ->setLatitude($point->getLatitude())
             ->setAltitude($point->getAltitude());

        return $addr;
    }

}