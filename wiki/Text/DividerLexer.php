<?php
/**
 * Defines the T_Text_DividerLexer class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Visitor that parses dividers out of formatted text.
 *
 * A divider in formatted text is recognised as four or more consecutive dashes
 * at the start of a new line.
 *
 * @package wiki
 */
class T_Text_DividerLexer extends T_Text_LexerTemplate
{

    /**
     * Parses a piece of text to capture any dividers.
     *
     * @param T_Text_Parseable $element
     */
    protected function parse(T_Text_Parseable $element)
    {
        $content = $element->getContent();
        /* for performance reasons, we avoid executing the regex if there is no
           quad-dash in the text. */
        if (strpos($content,'----')===false) return;
        $lf = '(?:\r\n|\n|\x0b|\r|\f|\x85|^|$)';
        $regex = '/'.$lf.'[ \t]*[-]{4,}[ \t]*'.$lf.'/';
        $matches = null;
        $num = preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE);
        if ($num < 1) return;  /* no dividers */
        $offset = 0;
        /* Note that the offset produced from preg_match_all is in bytes, not
           unicode characters. Therefore, in the following section we do NOT use
           the mb_* functions to assess length, as we are working in bytes not
           characters. */
        for ($i=0; $i<$num; $i++) {
            /* pre content */
            if ($offset<$matches[0][$i][1]) {
                $pre = substr($content,$offset,$matches[0][$i][1]-$offset);
                $element->addChild(new T_Text_Plain($pre));
            }
            /* divider */
            $element->addChild(new T_Text_Divider(null));
            /* update offset */
            $offset = $matches[0][$i][1] + strlen($matches[0][$i][0]);
        }
        /* post content */
        if ($offset<strlen($content)) {
            $post = substr($content,$offset);
            $element->addChild(new T_Text_Plain($post));
        }
        /* reset original content */
        $element->setContent(null);
    }

    /**
     * Visit a formatted text node.
     *
     * @param T_Text_Element $element
     */
    function visitTextPlain($element)
    {
        $this->parse($element);
    }

    /**
     * Visit a quote.
     *
     * @param T_Text_Element $element
     */
    function visitTextQuote($element)
    {
        $this->parse($element);
    }

}