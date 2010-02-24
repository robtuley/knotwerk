<?php
/**
 * Defines the T_Google_Geocode class.
 *
 * @package geo
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Uses the Google Maps API to geocode a point.
 *
 * @package geo
 * @see http://code.google.com/apis/maps/documentation/geocoding/index.html
 */
class T_Google_GeoCode extends T_Filter_Skeleton implements T_Filter_GeoCode
{

    /**
     * Your google maps key.
     *
     * @var string
     */
    protected $key;

    /**
     * Whether you are using a GPS sensor.
     *
     * @var bool
     */
    protected $sensor;

    /**
     * Bias to a country TLD code.
     *
     * @var string
     */
    protected $cc_tld = false;

    /**
     * Create geocoding filter.
     *
     * @param string $key  google api maps key
     * @param bool $sensor  whether you are using a sensor (e.g. GPS) to fix user position
     * @param T_Filter $prior  prior filter
     */
    function __construct($key,$sensor=false,$prior=null)
    {
        parent::__construct($prior);
        $this->key = $key;
        $this->sensor = (bool) $sensor;
    }

    /**
     * Bias the geocode to a particular country.
     *
     * @param string $code  ISO 3166-1 country code
     * @return T_Google_GeoCode  fluent interface
     */
    function biasToCountry($code)
    {
        if ($code) {
            $filter = new T_Filter_CcTld;
            $this->cc_tld = _transform($code,$filter);
        } else {
            $this->cc_tld = false;
        }
        return $this;
    }

    /**
     * Geocodes a string.
     *
     * @param string $value
     * @return T_Geo_Point  point
     */
    protected function doTransform($value)
    {
        // build url
        $url = 'http://maps.google.com/maps/geo?q='.urlencode($value).
                    '&output=csv'.
                    '&key='.urlencode($this->key).
                    '&oe=utf8'.
                    '&sensor='.($this->sensor ? 'true' : 'false');
        if ($this->cc_tld!==false) $url .= '&gl='.urlencode($this->cc_tld);

        // make request
        // (set up a CURL request, telling it not to spit back headers)
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $data = curl_exec($ch);
        curl_close($ch);

        // process data
        // (response_code,accuracy,longitude,latitude)
        if (substr($data,0,3)==='200') {
            $data = explode(',',$data);
            $point = new T_Geo_Point($data[3],$data[2]);
        } else {
            throw new T_Exception_Filter("Failed to geocode $value with response $data");
        }
        return $point;
    }

}