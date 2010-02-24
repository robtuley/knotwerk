<?php
/**
 * Contains the T_Text_HeaderLexer class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Visitor that parses formatted text into headers and text.
 *
 * A header is created where appropriate syntax is found and the surrounding
 * text in placed in a formatted text holder. Approiate syntax is:
 *
 *   1. A single line break precedes the title, maybe be followed by whitespace.
 *   2. Header content is prefixed and suffixed by matching equal-sign delimiters
 *        e.g. level 2 header '=== header content ==='
 *   3. Suffixed by a new line break (preceded by any amount of whitespace).
 *
 * This lexer is designed to be implemented *first*, before other lexers have
 * completed any sub-parsing and so doesn't contain visitor methods for all
 * wiki element types.
 *
 * @package wiki
 */
class T_Text_HeaderLexer extends T_Text_LexerTemplate
{

    /**
     * Parses a piece of text into a number of headers.
     *
     * @param T_Text_Parseable $element
     */
    protected function parse(T_Text_Parseable $element)
    {
        $lf = '(?:\r\n|\n|\x0b|\r|\f|\x85|^|$)';
        $regex = '/'.$lf.'\s*'.     /* line breaks (inc start or end) */
                 '(\={2,7})'.       /* equals sign delimiters, placed in backreference */
                 '(.+)'.            /* some content */
                 '\1'.              /* close title delimiters */
                 '\s*'.$lf.'/u';    /* line feed at end */
        $content = $element->getContent();
        $num = preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE);
        if ($num < 1) {
            return;  /* no change, as no headers */
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
            /* header */
            $level = strlen($matches[1][$i][0])-1;
            $element->addChild(new T_Text_Header($level,mb_trim($matches[2][$i][0])));
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

}