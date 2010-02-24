<?php
/**
 * Defines the T_Text_EmphLexer class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Visitor that parses formatted text into emphasised text.
 *
 * A piece of emphasised text is formed from parsed from the delimiters
 * of a double asterisk quote mark.
 * <samp>
 * This is a **piece of emphasised text** contained in double asterisks.
 * </samp>
 *
 * @package wiki
 */
class T_Text_EmphLexer extends T_Text_LexerTemplate
{

    /**
     * Parses a piece of text into a number of bits of emphasised text.
     *
     * @param T_Text_Parseable $element
     */
    protected function parse(T_Text_Parseable $element)
    {
        $content = $element->getContent();
        /* for performance reasons, we avoid executing the regex if there is no
           double asterisk in the text. */
        if (strpos($content,'**')===false) return;
        $regex = '/\*\*(.+?)\*\*/u';  // question mark makes dot repetition LAZY
        $matches = null;
        $num = preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE);
        if ($num < 1) return;  /* no change, as no emphasised text */
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
            /* emphasised */
            $emph = new T_Text_Emph($matches[1][$i][0]);
            $element->addChild($emph);
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

}