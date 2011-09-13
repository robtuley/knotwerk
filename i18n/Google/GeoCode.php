<?php
/**
 * Uses the Google Maps API to geocode a point.
 *
 * @package geo
 * @see http://code.google.com/apis/maps/documentation/geocoding/index.html
 */
class T_Google_GeoCode extends T_Filter_Skeleton implements T_Filter_GeoCode
{

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
    function __construct($sensor=false,$prior=null)
    {
        parent::__construct($prior);
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
        $url = 'http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($value).
                    '&sensor='.($this->sensor ? 'true' : 'false');
        if ($this->cc_tld!==false) $url .= '&region='.urlencode($this->cc_tld);

        // make request
        // (set up a CURL request, telling it not to spit back headers)
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);

        // process data
        $point = false;
        if ($code==200) {
            $data = json_decode($data,true);
            if ($data['status']=='OK') {
                $geo = reset($data['results']);
                $geo = $geo['geometry']['location'];
                $point = new T_Geo_Point($geo['lng'],$geo['lat']);
            }
        }
        if (!$point)
            throw new T_Exception_Filter("Failed to geocode $value");
        return $point;
    }

}