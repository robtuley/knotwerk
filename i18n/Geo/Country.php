<?php
/**
 * Defines the T_Geo_Country class.
 *
 * @package geo
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Country.
 *
 * @package geo
 */
class T_Geo_Country
{

    /**
     * Country ID.
     *
     * @var int
     */
    protected $id;

    /**
     * Two character country code.
     *
     * @var string
     */
    protected $code;

    /**
     * Country name.
     *
     * @var string
     */
    protected $name;

    /**
     * Country url string.
     *
     * @var string
     */
    protected $url;

    /**
     * Create country object.
     *
     * @param int $id  ID
     * @param string $code  two character country code
     * @param string $name  country name
     * @param string $url   url-safe country name
     */
    function __construct($id,$code,$name,$url)
    {
        $this->id = $id;
        $this->code = strtoupper($code);
        $this->name = $name;
        $this->url  = $url;
    }

    /**
     * Gets the country ID.
     *
     * @return int
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Gets the country code.
     *
     * @param function $filter  filter to apply to output
     * @return string  two letter country code.
     */
    function getCode($filter=null)
    {
        return _transform($this->code,$filter);
    }

    /**
     * Gets a url-safe country identifying string.
     *
     * @param function $filter  filter to apply to output
     * @return string  URL string
     */
    function getUrl($filter=null)
    {
        return _transform($this->url,$filter);
    }

    /**
     * Gets the country name.
     *
     * @param function $filter  filter to apply to output
     * @return string  filtered country name
     */
    function getName($filter=null)
    {
        return _transform($this->name,$filter);
    }

}
