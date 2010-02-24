<?php
/**
 * Contains the T_Xhtml_TextNoWrap class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A visitor to render formatted text as XHTML ignoring explicit line breaks.
 *
 * @package wiki
 */
class Xhtml_TextWrapEol extends T_Xhtml_Text
{

    /**
     * Visit the paragraph mode.
     *
     * @param T_Text_Paragraph $node
     */
    function visitTextParagraph(T_Text_Paragraph $node)
    {
        $this->xhtml .= EOL.'<p>'.$node->getContent($this->filter);
        $this->registerForPostMethod($node);
    }

    /**
     * Close paragraph tag.
     *
     * @param T_Text_Paragraph $node
     */
    function postTextParagraph(T_Text_Paragraph $node)
    {
        $this->xhtml .= '</p>';
    }

}
