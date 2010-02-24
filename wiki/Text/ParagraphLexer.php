<?php
/**
 * Contains the T_Text_ParagraphLexer class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Visitor that parses formatted text into paragraphs.
 *
 * A new paragraph is formed from parsing the content into separate blocks
 * divided on new line characters. The parser ignores all pieces that are:
 *   (a) section header
 *   (b) list           <-- TODO
 *   (c) already in a paragraph
 *
 * @package wiki
 */
class T_Text_ParagraphLexer extends T_Text_LexerTemplate
{

    /**
     * Parses a piece of text into a number of paragraphs.
     *
     * @param T_Text_Parseable $element
     */
    protected function parse(T_Text_Parseable $element)
    {
        $regex = '/(?:(?:\r\n|\n|\x0b|\r(?!\n)|\f|\x85)\s*){2,}/';
        /* Matches 2+ newline character: LF, CR CRLF and unicode linebreaks.
           We can't use the more efficient '\R' here as:
             (a) it is only supported by PCRE 7.0+
             (b) we allow '\r' but need to make sure \r\n is not interpreted as
                 a double line break.
             (c) meed to allow whitespace between line breaks */
        $paragraphs = preg_split($regex,$element->getContent());
        foreach ($paragraphs as $p) {
            $p = trim($p);
            if (strlen($p)>0) {
        	   $element->addChild(new T_Text_Paragraph($p));
            }
        }
        $element->setContent(null);
    }

    /**
     * Visit a formatted text node.
     *
     * @param T_Text_Element $element
     */
    function visitTextPlain($element)
    {
        if (!$element->isContainedBy('T_Text_Paragraph') &&
            !$element->isContainedBy('T_Text_Header')       ) {
            $this->parse($element);
        }
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