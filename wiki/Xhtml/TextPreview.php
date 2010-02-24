<?php
/**
 * Defines the T_Xhtml_TextPreview class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A visitor to render a preview of formatted text as XHTML.
 *
 * @package wiki
 */
class T_Xhtml_TextPreview extends T_Xhtml_Text
{

    /**
     * Reference to the limiting filter.
     *
     * @var T_Filter_LimitedLengthText
     */
    protected $limiter;

    /**
     * Table row is on/off;
     *
     * @var bool
     */
    protected $table_row = false;

    /**
     * Initialise visitor.
     *
     * @param int $max_len  preview maximum length (characters)
     * @param T_Url $root   root URL for links
     */
    function __construct($max_len,T_Url $root)
    {
        parent::__construct($root);
        $delimiter = '\s'.preg_quote('.,?:;!');
        $this->filter = new T_Filter_LimitedLengthText($max_len,$delimiter,' ...',$this->filter);
        $this->limiter = $this->filter;
          // ^ the limiting filter reference is stored separately to add flexibility: other
          //   filters can be added (and indeed, the paragraph visitor modifies the filter
          //   already), but the link to the main limiter is not lost.
    }

    /**
     * Visit a paragraph node.
     *
     * @param T_Text_Paragraph $node
     */
    function visitTextParagraph(T_Text_Paragraph $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextParagraph($node);
        }
    }

    /**
     * Visit a formatted text node.
     *
     * @param T_Text_Plain $node
     */
    function visitTextPlain(T_Text_Plain $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextPlain($node);
        }
    }

    /**
     * Visit a header node.
     *
     * @param T_Text_Header $node
     */
    function visitTextHeader(T_Text_Header $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextHeader($node);
        }
    }

    /**
     * Visit an external link node.
     *
     * @param T_Text_ExternalLink $node
     */
    function visitTextExternalLink(T_Text_ExternalLink $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextExternalLink($node);
        }
    }

    /**
     * Visit an internal link node.
     *
     * @param T_Text_InternalLink $node
     */
    function visitTextInternalLink(T_Text_InternalLink $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextInternalLink($node);
        }
    }

    /**
     * Visit an emphasised text node.
     *
     * @param T_Text_Emph $node
     */
    function visitTextEmph(T_Text_Emph $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextEmph($node);
        }
    }

    /**
     * Visit a quotation.
     *
     * @param T_Text_Quote $node
     */
    function visitTextQuote(T_Text_Quote $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextQuote($node);
        }
    }

    /**
     * Visit a citation.
     *
     * @param T_Text_Citation $node
     */
    function visitTextCitation(T_Text_Citation $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextCitation($node);
        }
    }

    /**
     * Visit a list item.
     *
     * @param T_Text_ListItem $node
     */
    function visitTextListItem(T_Text_ListItem $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextListItem($node);
        }
    }

    /**
     * Visit a list.
     *
     * @param T_Text_List $node
     */
    function visitTextList(T_Text_List $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextList($node);
        }
    }

    /**
     * Visit a table.
     *
     * @param T_Text_Table $node
     */
    function visitTextTable(T_Text_Table $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextTable($node);
        }
    }

    /**
     * Visit a table row.
     *
     * @param T_Text_Table $node
     */
    function visitTextTableRow(T_Text_TableRow $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            $this->table_row = true;  // note have started table row
            parent::visitTextTableRow($node);
        }
    }

	/**
     * Close table row tag.
     *
     * Note that we override this to make sure we track properly whether a
     * table row is already open in the preview.
     *
     * @param T_Text_TableRow $node
     */
    function postTextTableRow(T_Text_TableRow $node)
    {
        $this->table_row = false;
        parent::postTextTableRow($node);
    }

    /**
     * Visit a table cell.
     *
     * In a preview, table *rows* are considered atomic, so if we are displaying this
     * row we need to simply render all the cells. If we are not displaying this row
     * we need to skip all the table cells.
     *
     * @param T_Text_TableCell $node
     */
    function visitTextTableCell(T_Text_TableCell $node)
    {
        if ($this->table_row) {
            parent::visitTextTableCell($node);
        }
    }

	/**
     * Visit a superscript.
     *
     * @param T_Text_Superscript $node
     */
    function visitTextSuperscript(T_Text_Superscript $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextSuperscript($node);
        }
    }

	/**
     * Visit a subscript.
     *
     * @param T_Text_Superscript $node
     */
    function visitTextSubscript(T_Text_Subscript $node)
    {
        if (!$this->limiter->hasReachedLimit()) {
            parent::visitTextSubscript($node);
        }
    }

    /**
     * Do not include embedded resources in a preview.
     *
     * @param T_Text_Resource $node
     */
    function visitTextResource(T_Text_Resource $node) { }

}
