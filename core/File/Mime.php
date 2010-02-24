<?php
/**
 * Contains the T_File_Mime class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Mime type.
 *
 * Encapulates a file MIME type.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_File_Mime extends T_Mime
{

    /**
     * Preferred file extension.
     *
     * @var array
     */
    protected static $lookup = array( parent::BINARY => '',
                                      parent::TEXT => 'txt',
                                      parent::XHTML => 'htm',
                                      parent::CSS => 'css',
                                      parent::WORD => 'doc',
                                      parent::EXCEL => 'xls',
                                      parent::PDF => 'pdf',
                                      parent::JPEG => 'jpg',
                                      parent::PNG => 'png',
                                      parent::GIF => 'gif',
                                      parent::PHP => 'php',
                                      parent::ZIP => 'zip',
                                      parent::JS => 'js',
                                      parent::XML => 'xml' );

    /**
     * Possible ext aliases.
     *
     * Each MIME type might be associated with a number of possible file
     * extensions. The preferred extension is stored in $ext variable, this
     * array stores possible url aliases.
     *
     * @var array
     */
    protected static $alias = array( 'html' => parent::XHTML,
                                     'jpeg' => parent::JPEG,
                                     'php'  => parent::PHP,
                                     'php5' => parent::PHP,
                                     'phps' => parent::PHP,
                                     'docx' => parent::WORD,
                                     'xlsx' => parent::EXCEL,
                                     'tpl' => parent::TEXT,
                                     'db' => parent::BINARY  );

    /**
     * Extension this instance initialised by.
     *
     * This is stored to maintain valid filenames when using MIME objects. It
     * is the exact extension the type was created with.
     *
     * @var string
     */
    protected $ext;

    /**
     * Encapsulate MIME type.
     *
     * @param string $ext  file extension
     */
    function __construct($ext)
    {
        $this->ext = $ext;
        $ext = strtolower((string) $ext);
        $type = array_search($ext,self::$lookup,true);
        if ($type !== false) {
            parent::__construct($type);
        } elseif (array_key_exists($ext,self::$alias)) {
            parent::__construct(self::$alias[$ext]);
        } else {
            parent::__construct(parent::BINARY);
        }
    }

    /**
     * Set type.
     *
     * @param int $type  MIME type integer key
     * @return T_File_Mime  fluent interface
     */
    function setType($type)
    {
        if (!$this->isType($type) || !isset(self::$lookup[$type])) {
            throw new InvalidArgumentException('Illegal file MIME type '.$type);
        }
        $this->type = $type;
        $this->ext = self::$lookup[$type];
        return $this;
    }

    /**
     * Gets the extension object was initialised with.
     *
     * @param function $filter  optional filter to apply
     * @return string  file extension
     */
    function getExt($filter=null)
    {
        return _transform($this->ext,$filter);
    }

}

