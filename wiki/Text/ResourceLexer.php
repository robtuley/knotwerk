<?php
/**
 * Contains the T_Text_ResourceLexer class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Visitor that parses embedded resources from formatted text.
 *
 * May be to a resource such as an image, flash video, etc. and is created
 * where appropriate wiki markup is discovered. Such syntax is:
 *
 * <code>
 * ... !http://knotwerk.com/img/logo.jpg! ...
 * </code>
 *
 *   1. An exclamation mark opens the embedded resource.
 *   2. The URL follows
 *        - external formats are http://..,https://..
 *        - internal links simply start with a forward slash !/img/logo.jpg!
 *   3. (optionally) a space follows the URL and is followed by the alt text
 *   5. Link is closed by a closing exclamation mark.
 *
 * This lexer is designed to be implemented after header and paragraph lexers.
 *
 * @package wiki
 */
class T_Text_ResourceLexer extends T_Text_LexerTemplate
{

    /**
     * Parses embedded links out from the text.
     *
     * @param T_Text_Element $element
     */
    protected function parse(T_Text_Parseable $element)
    {
        $lf = '(?:\r\n|\n|\x0b|\r|\f|\x85|^|$)';
        $url_prefix = '(?>http:\/\/|https:\/\/|\/)';
          /* use atomic grouping as minor performance incentive */
        $regex = '/'.$lf.'\s*\!'.          /* opening exclamation mark on new line (or start of text) */
                 '('.$url_prefix.'[^\s]+)'.  /* url */
                 '(\s[^\!]+)?'.              /* (optional) descriptive text */
                 '\!/u';                     /* closing exclamation mark */
        $content = $element->getContent();
        $matches = null;
        $num = preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE);
        if ($num < 1) {
            return;  /* no change, as no embedded links */
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
            $text = isset($matches[2][$i][0]) ? $matches[2][$i][0] : null;
            $link = new T_Text_Resource($text,$url);
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
     * Allow embedded resources within a quote.
     *
     * @param T_Text_Element $element
     */
    function visitTextQuote($element)
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
     * Allow an image at the start of a list item (i.e. list of resources).
     *
     * @param T_Text_Element $element
     */
    function visitTextListItem($element)
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