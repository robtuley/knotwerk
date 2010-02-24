<?php
/**
 * Contains the Doc_TextVisitor class.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Visits formatted text file.
 *
 * @package docs
 */
class Xhtml_Text extends Xhtml_TextWrapEol
{

    function visitPhp($node)
    {
        $f = new T_Xhtml_Php();
        $this->xhtml .= '<div class="code">'.
                        _transform($node->getContent(),$f).
                        '</div>';
    }

    function visitLiteral($node)
    {
        $this->xhtml .= '<pre>'.
                        _transform($node->getContent(),$this->filter).
                        '</pre>';
    }

}
