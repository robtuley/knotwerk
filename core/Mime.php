<?php
/**
 * Contains the T_Mime class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Mime.
 *
 * Encapulates a MIME string.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Mime
{

    /**
     * Encapsulated MIME types.
     */
    const BINARY = 1;
    const TEXT = 2;
    const XHTML = 3;
    const CSS = 4;
    const WORD = 5;
    const EXCEL = 6;
    const PDF = 7;
    const JPEG = 8;
    const PNG = 9;
    const GIF = 10;
    const PHP = 11;
    const ZIP = 12;
    const JS = 13;
    const XML = 14;
    const FORM_URL_ENCODED = 15;
    const FORM_MULTIPART = 16;

    /**
     * MIME text.
     *
     * @var array
     */
    protected static $mime = array( self::BINARY => 'application/octet-stream',
                                    self::TEXT => 'text/plain',
                                    self::XHTML => 'text/html',
                                    self::CSS => 'text/css',
                                    self::WORD => 'application/msword',
                                    self::EXCEL => 'application/excel',
                                    self::PDF => 'application/pdf',
                                    self::JPEG => 'image/jpeg',
                                    self::PNG => 'image/png',
                                    self::GIF => 'image/gif',
                                    self::PHP => 'application/x-httpd-php',
                                    self::ZIP => 'application/zip',
                                    self::JS => 'text/javascript',
                                    self::XML => 'application/xml',
                                    self::FORM_URL_ENCODED => 'application/x-www-form-urlencoded',
                                    self::FORM_MULTIPART => 'multipart/form-data',
                                  );

    /**
     * MIME str alias.
     *
     * @var array
     */
    protected static $mime_alias = array( 'text/xml' => self::XML,
                                          'application/vnd.ms-excel' => self::EXCEL,
                                          );

    /**
     * Creates a MIME object from a MIME string.
     *
     * @param string $str
     */
    static function getByString($str)
    {
        $type = array_search($str,self::$mime,false);
        if ($type===false) {
            if (isset(self::$mime_alias[$str])) {
                return new T_Mime(self::$mime_alias[$str]);
            }
            return new T_Mime(self::BINARY);
        } else {
            return new T_Mime($type);
        }
    }

    /**
     * Type encapsulated by this instance.
     *
     * @var unknown_type
     */
    protected $type;

    /**
     * Encapsulate MIME type.
     *
     * @param string $ext  file extension
     */
    function __construct($type)
    {
        if (!$this->isType($type)) {
            throw new InvalidArgumentException("Invalid type $type");
        }
        $this->type = $type;
    }

    /**
     * Get integer type key.
     *
     * @return int  MIME type integer key
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * Get MIME type string representation.
     *
     * @return string  MIME type representation
     */
    function __toString()
    {
        return self::$mime[$this->type];
    }

    /**
     * Whether a value is a type.
     *
     * @param int $type
     * @return bool
     */
    protected function isType($type)
    {
        return isset(self::$mime[$type]);
    }

}

