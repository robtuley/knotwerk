<?php
/**
 * Defines the T_Geo_Point class.
 *
 * @package geo
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A geographical point (usually defined by WGS84 co-ordinates).
 *
 * @package geo
 */
class T_Geo_Point
{

    /**
     * Longitude (degrees).
     *
     * @var float
     */
    protected $longitude;

    /**
     * Latitude (degrees)
     *
     * @var float
     */
    protected $latitude;

    /**
     * Altitude (nearest metre above "sea level").
     *
     * @var int
     */
    protected $altitude;

    /**
     * Create point.
     *
     * @param float $longitude  degrees longitude
     * @param float $latitude  degrees latitude
     * @param int $altitude  altitude to nearest metre
     */
    function __construct($longitude,$latitude,$altitude=null)
    {
        $this->longitude = $longitude;
        $this->latitude = $latitude;
        $this->altitude = $altitude;
    }

    /**
     * Gets the longitude.
     *
     * @param function $filter  optional filter
     * @return float  degrees
     */
    function getLongitude($filter=null)
    {
        return _transform($this->longitude,$filter);
    }

    /**
     * Change the longitude.
     *
     * @param float $deg
     * @return T_Geo_Point
     */
    function setLongitude($deg)
    {
        $this->longitude = $deg;
        return $this;
    }

    /**
     * Gets the latitude.
     *
     * @param function $filter  optional filter
     * @return float  degrees
     */
    function getLatitude($filter=null)
    {
        return _transform($this->latitude,$filter);
    }

    /**
     * Change the latitude.
     *
     * @param float $deg
     * @return T_Geo_Point
     */
    function setLatitude($deg)
    {
        $this->latitude = $deg;
        return $this;
    }

    /**
     * Gets the altitude.
     *
     * @param function $filter  optional filter
     * @return int  metres above sea level
     */
    function getAltitude($filter=null)
    {
        return _transform($this->altitude,$filter);
    }

    /**
     * Change the altitude.
     *
     * @param int $metres
     * @return T_Geo_Point
     */
    function setAltitude($metres)
    {
        $this->altitude = $metres;
        return $this;
    }

}
