<?php
/**
 * Defines the T_Text_Lexer class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Contains a collection of wiki syntax lexers.
 *
 * @package core
 */
class T_Text_Lexer extends T_Filter_Skeleton
{

    /**
     * Granular lexer types.
     */
    const HEADER = 1;
    const QUOTE = 2;
    const EMPH = 4;
    const LINK = 8;
    const RESOURCE = 16;
    const ORDERED_LIST = 32;
    const UNORDERED_LIST = 64;
    const PARAGRAPH = 128;
    const TABLE = 256;
    const DIVIDER = 512;
    const SUPERSUB = 1024;

    /**
     * Grouped lexer modes.
     */
    const ALL = 2047; // == bindec('11111111111');

    /**
     * Lexer mode.
     *
     * @var int
     */
    protected $mode;

    /**
     * Create lexer filter.
     *
     * @param int $mode
     * @param function $filter
     */
    function __construct($mode=self::ALL,$filter=null)
    {
        $this->mode = $mode;
        parent::__construct($filter);
    }

    /**
     * Transform plain text into a parsed formatted text object.
     *
     * @param string $value
     * @return T_Text_Plain  formatted text
     */
    function doTransform($value)
    {
        $fmt = new T_Text_Plain($value);
        if ($this->is(self::HEADER))    $fmt->accept(new T_Text_HeaderLexer());
        if ($this->is(self::QUOTE))     $fmt->accept(new T_Text_QuoteLexer());
        if ($this->is(self::TABLE))     $fmt->accept(new T_Text_TableLexer());
        if ($this->is(self::DIVIDER))   $fmt->accept(new T_Text_DividerLexer());
         // ^ dividers must be lexed *after* tables, and this is safe enough as
         //   at this point no T_Text_Plain items are table cell children
        if ($this->is(self::RESOURCE))  $fmt->accept(new T_Text_ResourceLexer());
        if ($this->is(self::ORDERED_LIST|self::UNORDERED_LIST)) {
            $fmt->accept(new T_Text_ListLexer($this->mode));
        }
        if ($this->is(self::PARAGRAPH)) $fmt->accept(new T_Text_ParagraphLexer());
        if ($this->is(self::LINK))      $fmt->accept(new T_Text_LinkLexer());
        if ($this->is(self::RESOURCE))  $fmt->accept(new T_Text_ResourceLexer());
                                        // ^ called twice to handle 1st time standalone resources, and
                                        //   2nd time resources embedded in links or list items for example
        if ($this->is(self::EMPH))      $fmt->accept(new T_Text_EmphLexer());
        if ($this->is(self::SUPERSUB))  $fmt->accept(new T_Text_SuperSubscriptLexer());
        return $fmt;
    }

    /**
     * Whether filter has a particular lexer mode on.
     *
     * @param int $mode
     * @return bool
     */
    protected function is($mode)
    {
        return (bool) ($this->mode & $mode); // note use of bitwise operator
    }

}