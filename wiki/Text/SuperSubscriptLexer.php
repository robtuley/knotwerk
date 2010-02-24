<?php
/**
 * Defines the T_Text_SuperSubscriptLexer class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Visitor that parses super- amd subscripts from formatted text.
 *
 * A superscript is formed by using the `^` character. By default, the hat character will
 * make only the following character superscripted, if you want to make more than 1 character
 * superscripted you need to enclose the superscript text in curly brackets. Subscript is the
 * treated in exactly the same way except it uses the `_` character instead.
 *
 * ~~~~~~
 * The 1^{st} trapoline is 23.5m^2 in size.
 * H_2O is water, and H_{20}0 is impossible!
 * ~~~~~~
 *
 * @package wiki
 */
class T_Text_SuperSubscriptLexer extends T_Text_LexerTemplate
{

    /**
     * Parses a piece of text into a number of bits of emphasised text.
     *
     * @param T_Text_Parseable $element
     */
    protected function parse(T_Text_Parseable $element)
    {
        $content = $element->getContent();
        $regex = '/(\_|\^)([^ \t\{\^-]|\{[^\^-]+?\})/u';
                                     // ^ question mark makes dot repetition LAZY
        $matches = null;
        $num = preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE);
        if ($num < 1) return;  /* no change, as no super/supercripts text */
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
            /* super/sub */
            $ss = $matches[2][$i][0];  // now remove any starting/ending curly brackets
            $ss = (strncmp($ss,'{',1)===0) ? mb_substr($ss,1,mb_strlen($ss)-2) : $ss;
            if (strcmp($matches[1][$i][0],'^')===0) {
                $script = new T_Text_Superscript($ss);
            } else {
                $script = new T_Text_Subscript($ss);
            }
            $element->addChild($script);
            /* update offset */
            $offset = $matches[0][$i][1]+strlen($matches[0][$i][0]);
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
     * Visit a header node.
     *
     * @param T_Text_Header $element
     */
    function visitTextHeader($element)
    {
        $this->parse($element);
    }

    /**
     * Visit a citation.
     *
     * @param T_Text_Element $element
     */
    function visitTextCitation($element)
    {
        $this->parse($element);
    }

    /**
     * Visit a list item.
     *
     * @param T_Text_Element $element
     */
    function visitTextListItem($element)
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

    /**
     * Visit a paragraph.
     *
     * @param T_Text_Element $element
     */
    function visitTextParagraph($element)
    {
        $this->parse($element);
    }

    /**
     * Visit an external link.
     *
     * @param T_Text_ExternalLink $element
     */
    function visitTextExternalLink($element)
    {
        $this->parse($element);
    }

    /**
     * Visit an internal link.
     *
     * @param T_Text_InternalLink $element
     */
    function visitTextInternalLink($element)
    {
        $this->parse($element);
    }

    /**
     * Visit a table cell.
     *
     * @param T_Text_TableCell $element
     */
    function visitTextTableCell($element)
    {
        $this->parse($element);
    }

    /**
     * Visit a piece of emphasised text.
     *
     * @param T_Text_Emph $element
     */
    function visitTextEmph($element)
    {
        $this->parse($element);
    }

}