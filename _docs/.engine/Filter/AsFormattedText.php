<?php
/**
 * Contains the Doc_AsFormattedText class.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Parses text content into formatted text.
 *
 * @package docs
 */
class Filter_AsFormattedText extends T_Filter_Skeleton
{

    /**
     * Converts text to formatted text.
     *
     * @param string $value  content string
     * @return T_Text_Plain  formatted text
     */
    protected function doTransform($value)
    {
        $formatted = new T_Text_Plain($value);
        $formatted->accept(new Text_LiteralLexer()); // before PHP
        $formatted->accept(new Text_PhpLexer());
        $formatted->accept(new T_Text_HeaderLexer());
        $formatted->accept(new T_Text_TableLexer());
        $formatted->accept(new T_Text_QuoteLexer());
        $formatted->accept(new T_Text_ListLexer());
        $formatted->accept(new T_Text_ParagraphLexer());
        $formatted->accept(new T_Text_EmphLexer());
        $formatted->accept(new T_Text_LinkLexer());
        return $formatted;
    }

}
