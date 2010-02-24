<?php
/**
 * Contains the T_Text_LinkLexer class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Visitor that parses formatted text, paragraphs and headers into links.
 *
 * A Link is created where appropriate wiki markup is discovered. Such syntax is:
 *
 * ~~~~
 * ... [http://openknot.com Openknot Site] ...
 * ~~~~
 *
 *   - A square bracket opens the link.
 *   - The URL follows
 *        * external formats are http://..,https://..,'ftp://...',mailto://...'
 *        * internal links simplay start with a forward slash [/ref Reference]
 *   - A space follows the URL.
 *   - The link text follows the space (and may include spaces itself).
 *   - Link is closed by a closing square bracket.
 *
 * This lexer is designed to be implemented after header and paragraph lexers.
 *
 * @package wiki
 */
class T_Text_LinkLexer extends T_Text_LexerTemplate
{

    /**
     * Parses a piece of text into a number of headers.
     *
     * @param T_Text_Parseable $element
     */
    protected function parse(T_Text_Parseable $element)
    {
        $url_prefix = '(?>http:\/\/|https:\/\/|ftp:\/\/|mailto:|\/)';
          /* use atomic grouping as minor performance incentive */
        $regex = '/\['.                      /* opening square bracket */
                 '('.$url_prefix.'[^\s]+)'.  /* url */
                 '\s'.                       /* space */
                 '([^\]]+)'.                 /* link text */
                 '\]/u';                     /* closing bracket */
        $content = $element->getContent();
        $matches = null;
        $num = preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE);
        if ($num < 1) {
            return;  /* no change, as no links */
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
            /* link */
            $url = $matches[1][$i][0];
            $text = $matches[2][$i][0];
            if (strncmp($url,'/',1)===0) {
                $link = new T_Text_InternalLink($text,$url);
            } else {
                $link = new T_Text_ExternalLink($text,$url);
            }
            $element->addChild($link);
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
     * Visit a citation node.
     *
     * @param T_Text_Element $element
     */
    function visitTextCitation($element)
    {
        $this->parse($element);
    }

    /**
     * Visit a header.
     *
     * @param T_Text_Element $element
     */
    function visitTextHeader($element)
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
     * Visit a list item.
     *
     * @param T_Text_Element $element
     */
    function visitTextListItem($element)
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
     * Visit a section of emphasised text.
     *
     * @param T_Text_Element $element
     */
    function visitTextEmph($element)
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
