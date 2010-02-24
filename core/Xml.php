<?php
/**
 * Contains the class T_Xml.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * simpleXML class wrapper.
 *
 * A wrapper class for the SimpleXmlElement class. The reason this wrapper
 * class is created is that the SimpleXml object's constructor cannot be
 * extended. This wrapper circumvents that restriction and is used for example
 * in T_Xhtml_UrlSitemap.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Xml
{

    /**
     * XML class wrapper.
     *
     * @var T_Xml_Element
     */
    protected $sxe;

    /**
     * Create XML base class.
     *
     * @param unknown_type $xml
     */
    function __construct($xml)
    {
        $this->sxe = new T_Xml_Element($xml);
    }

    /**
     * Transfer all method class to SimpleXml object.
     *
     * @param string $method  method name
     * @param array $args  method arguments
     * @return mixed
     */
    function __call($method, $args)
    {
        return call_user_func_array(array($this->sxe,$method),$args);
    }

    /**
     * Get SimpleXml element.
     *
     * @param string $property  child element name
     * @return T_Xml_Element child element property
     */
    function __get($property)
    {
        return $this->sxe->$property;
    }

    /**
     * Set value of child element.
     *
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    function __set($property,$value)
    {
        return $this->sxe->$property = $value;
    }

    /**
     * Get XML representation.
     *
     * @return string  XML string
     */
    function asXml()
    {
        return $this->sxe->asXML();
    }

    /**
     * Return XML string representation.
     *
     * @return string  XML string
     */
    function __toString()
    {
        return $this->asXml();
    }

}