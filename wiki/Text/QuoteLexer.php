<?php
/**
 * Contains the T_Text_QuoteLexer class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Visitor that parses quote text into a quote object with an optional citation.
 *
 * A quote can be written as wiki-like text in the fashion:
 * <code>
 * ""
 * This is some wise quoted words from Rob Tuley
 * "" Rob Tuley
 * </code>
 *
 * @package wiki
 */
class T_Text_QuoteLexer extends T_Text_LexerTemplate
{

    /**
     * Parses a piece of text into a number of quotations.
     *
     * @param T_Text_Parseable $element
     */
    protected function parse(T_Text_Parseable $element)
    {
        $delimit = preg_quote('""');
        $lf = '(?:\r\n|\n|\x0b|\r|\f|\x85|^|$)';
        $regex = '/'.$lf.$delimit.'\s*'.$lf.     /* double double quotes on a single line */
                 '(.+?)'.                        /* some content (question mark means LAZY match) */
                 $lf.$delimit.'([^'.$lf.']*)'.   /* closing double quotes, no line feeds */
                 $lf.'/su';                      /* line feed at end */
                   // note the trailing 's', this puts the regex in multi-line mode and
                   // means that the 'dot' in the middle matches newlines
        $content = $element->getContent();
        $num = preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE);
        if ($num < 1) {
            return;  /* no change, as no quotes */
        }
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
            /* quote */
            $quote = mb_trim($matches[1][$i][0]);
            $cite = mb_trim($matches[2][$i][0]);
            $element->addChild(new T_Text_Quote($cite,$quote));
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
        if (!$element->isContainedBy('T_Text_Header') &&
            !$element->isContainedBy('T_Text_Quote') ) {
                $this->parse($element);
        }
    }

}