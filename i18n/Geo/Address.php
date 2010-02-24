<?php
/**
 * Defines the T_Geo_Address class.
 *
 * @package geo
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Address.
 *
 * @package geo
 */
class T_Geo_Address extends T_Geo_Point
{

    /**
     * Line 1.
     *
     * @var string
     */
    protected $line_one;

    /**
     * Line 2.
     *
     * @var string
     */
    protected $line_two;

    /**
     * City.
     *
     * @var string
     */
    protected $city;

    /**
     * State
     *
     * @var string
     */
    protected $state;

    /**
     * Postal Code.
     *
     * @var string
     */
    protected $post_code;

    /**
     * Country.
     *
     * @var T_Geo_Country
     */
    protected $ctry;

    /**
     * Create address object.
     *
     * @param string $line1  line one
     * @param string $line2  line two
     * @param string $city  city or town
     * @param string $state  state or province
     * @param string $postcode  postal code or zip code
     * @param T_Geo_Country $country  country
     * @param float $longitude  degrees longitude
     * @param float $latitude  degrees latitude
     * @param int $altitude  altitude to nearest metre
     */
    function __construct($line1,$line2,$city,$state,$postcode,$country,
                         $longitude=null,$latitude=null,$altitude=null)
    {
        $this->line_one  = $line1;
        $this->line_two  = $line2;
        $this->city      = $city;
        $this->state     = $state;
        $this->post_code = $postcode;
        $this->ctry      = $country;
        parent::__construct($longitude,$latitude,$altitude);
    }

    /**
     * Gets the first line.
     *
     * @param function $filter  optional filter
     * @return string  line one of the address
     */
    function getLineOne($filter=null)
    {
        return _transform($this->line_one,$filter);
    }

    /**
     * Change address line one.
     *
     * @param string $text
     * @return T_Geo_Address
     */
    function setLineOne($text)
    {
        $this->line_one = $text;
        return $this;
    }

    /**
     * Gets the 2nd line.
     *
     * @param function $filter  optional filter
     * @return string line 2 of the address
     */
    function getLineTwo($filter=null)
    {
        return _transform($this->line_two,$filter);
    }

    /**
     * Change address line two.
     *
     * @param string $text
     * @return T_Geo_Address
     */
    function setLineTwo($text)
    {
        $this->line_two = $text;
        return $this;
    }

    /**
     * Gets the City/Town.
     *
     * @param function $filter  optional filter
     * @return string  city or town
     */
    function getCity($filter=null)
    {
        return _transform($this->city,$filter);
    }

    /**
     * Change city.
     *
     * @param string $city
     * @return T_Geo_Address
     */
    function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Gets the State.
     *
     * @param function $filter  optional filter
     * @return string  state
     */
    function getState($filter=null)
    {
        return _transform($this->state,$filter);
    }

    /**
     * Set state.
     *
     * @param string $state
     * @return T_Geo_Address
     */
    function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Gets the Postcode.
     *
     * @param function $filter  optional filter
     * @return string  postcode
     */
    function getPostCode($filter=null)
    {
        return _transform($this->post_code,$filter);
    }

    /**
     * Set postcode.
     *
     * @param string $postcode
     * @return T_Geo_Address
     */
    function setPostCode($code)
    {
        $this->post_code = $code;
        return $this;
    }

    /**
     * Gets the country.
     *
     * @return T_Geo_Country  country
     */
    function getCountry()
    {
        return $this->ctry;
    }

    /**
     * Set country.
     *
     * @param null|T_Geo_Country $ctry
     * @return T_Geo_Address
     */
    function setCountry($ctry)
    {
        $this->ctry = $ctry;
        return $this;
    }

    /**
     * Outputs the entire address as a string.
     *
     * This returns the address in a standard format, with the lines separated
     * by carriage returns.
     * Address line 1
     * Address line 2
     * City, State
     * Postal Code/Zip
     * Country
     *
     * @param T_Filter  output filter
     * @return string formatted address
     */
    function asString($filter=null)
    {
        $output = '';
        if (strlen($this->line_one)>0) $output .= $this->line_one.EOL;
        if (strlen($this->line_two)>0) $output .= $this->line_two.EOL;
        $line3 = array();
        if (strlen($this->city)>0) $line3[] = $this->city;
        if (strlen($this->state)>0) $line3[] = $this->state;
        if (count($line3)>0) $output .= implode(', ',$line3).EOL;
        if (strlen($this->post_code)>0) $output .= $this->post_code.EOL;
        if (!is_null($this->ctry)) $output .= $this->ctry->getName();
        return _transform(mb_trim($output),$filter);
    }

    /**
     * Convert object to string.
     *
     * @return string
     */
    function __toString()
    {
        return $this->asString();
    }
}
