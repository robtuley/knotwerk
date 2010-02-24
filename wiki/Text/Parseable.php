<?php
/**
 * Contains the T_Text_WithContent interface.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for a parseable wiki element with content.
 *
 * For an element to be parsed, it must have some content to examine, and
 * it must be able to be subdivided into a number of children (i.e. it must
 * be a composite).
 *
 * @license http://knotwerk.com/licence MIT
 */
interface T_Text_Parseable extends T_Text_Element,T_Composite
{

    /**
     * Gets the content.
     *
     * @param function $filter  optional filter
     * @return string  element content
     */
    function getContent($filter=null);

}