<?php
/**
 * Defines the T_Text_Resource class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A link to an external resource that is to be embedded (e.g. an image).
 *
 * @package wiki
 */
class T_Text_Resource extends T_Text_ExternalLink
{

    /**
     * Whether the resource is internal.
     *
     * @return bool
     */
    function isInternal()
    {
        return strncmp($this->url,'/',1)===0;
    }

    /**
     * Returns original formatted text.
     *
     * @return string  original formatting
     */
    function __toString()
    {
        $content = parent::__toString();
        return '!'.$this->getUrl().($content ? ' '.$content : '').'!';
    }

}