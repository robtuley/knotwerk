<?php
/**
 * Contains the T_Xhtml_Text class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A visitor to render formatted text as XHTML.
 *
 * @package wiki
 * @license http://knotwerk.com/licence MIT
 */
class T_Xhtml_Text implements T_Visitor
{

    /**
     * XHTML.
     *
     * @var string
     */
    protected $xhtml = '';

    /**
     * External link class.
     *
     * @var string
     */
    protected $ext_link_class = 'ext';

    /**
     * Set the external link class.
     *
     * @param string $class
     */
    function setExternalLinkClass($class)
    {
        $this->ext_link_class = $class;
    }

    /**
     * Depth of composite.
     *
     * @var int
     */
    protected $depth = 0;

    /**
     * Parent at any level.
     *
     * This array stores references to the composite parent for any level. i.e.
     * index 0 will be a T_Text_Plain, index 1 might be a T_Text_Paragraph, etc.
     *
     * @var array
     */
    protected $parent = array();

    /**
     * Escaping filter for XHTML entities.
     *
     * @var function
     */
    protected $filter;

    /**
     * Swap space for manipulation of filters.
     *
     * @var function
     */
    protected $f_swap_space;

    /**
     * Header level adjustment.
     *
     * @var int
     */
    protected $header_shift = 0;

    /**
     * Root URL.
     *
     * @var T_Url
     */
    protected $root_url;

    /**
     * Initialise visitor.
     *
     * @param T_Url $root  root URL for link rendering
     */
    function __construct(T_Url $root)
    {
        $this->root_url = $root;
        $this->filter = new T_Filter_Xhtml();
    }

    /**
     * Register method as requiring a post method.
     *
     * @param T_Text_Element $node
     */
    protected function registerForPostMethod(T_Text_Element $node)
    {
        if (!$node->isChildren()) {
            $name   = explode('_',get_class($node));
            array_shift($name);
            $method = 'post'.implode('',$name);
            $this->$method($node);
        } else {
            $this->parent[$this->depth] = $node;
        }
    }

    /**
     * Visit a quote node.
     *
     * @param T_Text_Quote $node
     */
    function visitTextQuote(T_Text_Quote $node)
    {
        $this->xhtml .= EOL.'<blockquote>';
        $this->registerForPostMethod($node);
    }

    /**
     * Close blockquote.
     *
     * @param T_Text_Quote $node
     */
    function postTextQuote(T_Text_Quote $node)
    {
        $this->xhtml .= EOL.'</blockquote>';
    }

    /**
     * Visit a citation node.
     *
     * @param T_Text_Citation $node
     */
    function visitTextCitation(T_Text_Citation $node)
    {
        $this->xhtml .= EOL.'<p class="cite"><cite>';
        $this->xhtml .= $node->getContent($this->filter);
        $this->registerForPostMethod($node);
    }

    /**
     * Close citation.
     *
     * @param T_Text_Quote $node
     */
    function postTextCitation(T_Text_Citation $node)
    {
        $this->xhtml .= '</cite></p>';
    }

    /**
     * Visit the paragraph mode.
     *
     * @param T_Text_Paragraph $node
     */
    function visitTextParagraph(T_Text_Paragraph $node)
    {
        // inside a paragraph, all newlines should be converted to <br /> tags.
        $this->f_swap_space = $this->filter;
        $this->filter = new T_Filter_EolToBr($this->filter);

        // render content
        $this->xhtml .= EOL.'<p>'.$node->getContent($this->filter);
        $this->registerForPostMethod($node);
    }

    /**
     * Close paragraph tag.
     *
     * @param T_Text_Paragraph $node
     */
    function postTextParagraph(T_Text_Paragraph $node)
    {
        $this->xhtml .= '</p>';

        // after a paragraph has finished, restore filter to original status.
        $this->filter = $this->f_swap_space;
        $this->f_swap_space = null;
    }

    /**
     * Visit the list item.
     *
     * @param T_Text_ListItem $node
     */
    function visitTextListItem(T_Text_ListItem $node)
    {
        $this->xhtml .= EOL.'<li>'.$node->getContent($this->filter);
        $this->registerForPostMethod($node);
    }

    /**
     * Close list item tag.
     *
     * @param T_Text_ListItem $node
     */
    function postTextListItem(T_Text_ListItem $node)
    {
        $this->xhtml .= '</li>';
    }

    /**
     * Visit a list.
     *
     * @param T_Text_List $node
     */
    function visitTextList(T_Text_List $node)
    {
        if ($node->is(T_Text_List::ORDERED)) {
            $this->xhtml .= EOL.'<ol>';
        } else {
            $this->xhtml .= EOL.'<ul>';
        }
        $this->registerForPostMethod($node);
    }

    /**
     * Close list tag.
     *
     * @param T_Text_List $node
     */
    function postTextList(T_Text_List $node)
    {
        if ($node->is(T_Text_List::ORDERED)) {
            $this->xhtml .= EOL.'</ol>';
        } else {
            $this->xhtml .= EOL.'</ul>';
        }
    }

    /**
     * Visit emphasised text.
     *
     * @param T_Text_Emph $node
     */
    function visitTextEmph(T_Text_Emph $node)
    {
        $this->xhtml .= '<em>'.$node->getContent($this->filter);
        $this->registerForPostMethod($node);
    }

    /**
     * Close emphasised tag.
     *
     * @param T_Text_Emph $node
     */
    function postTextEmph(T_Text_Emph $node)
    {
        $this->xhtml .= '</em>';
    }

    /**
     * Visit superscript text.
     *
     * @param T_Text_Superscript $node
     */
    function visitTextSuperscript(T_Text_Superscript $node)
    {
        $this->xhtml .= '<sup>'.$node->getContent($this->filter);
        $this->registerForPostMethod($node);
    }

    /**
     * Close superscript text.
     *
     * @param T_Text_Superscript $node
     */
    function postTextSuperscript(T_Text_Superscript $node)
    {
        $this->xhtml .= '</sup>';
    }

	/**
     * Visit subscript text.
     *
     * @param T_Text_Subscript $node
     */
    function visitTextSubscript(T_Text_Subscript $node)
    {
        $this->xhtml .= '<sub>'.$node->getContent($this->filter);
        $this->registerForPostMethod($node);
    }

    /**
     * Close subscript text.
     *
     * @param T_Text_Subscript $node
     */
    function postTextSubscript(T_Text_Subscript $node)
    {
        $this->xhtml .= '</sub>';
    }

    /**
     * Visit external link.
     *
     * @param T_Text_ExternalLink $node
     */
    function visitTextExternalLink(T_Text_ExternalLink $node)
    {
        $escape = new T_Filter_Xhtml();
        $this->xhtml .= '<a class="'.$this->ext_link_class.'" href="'.$node->getUrl($escape).'">';
        $this->xhtml .= $node->getContent($this->filter);
        $this->registerForPostMethod($node);
    }

    /**
     * Close link tag.
     *
     * @param T_Text_ExternalLink $node
     */
    function postTextExternalLink(T_Text_ExternalLink $node)
    {
        $this->xhtml .= '</a>';
    }

    /**
     * Visit internal link.
     *
     * @param T_Text_InternalLink $node
     */
    function visitTextInternalLink(T_Text_InternalLink $node)
    {
        $escape = new T_Filter_Xhtml();
        $this->xhtml .= '<a href="'.$this->root_url->getUrl($escape).
                                    $node->getUrl($escape).'">';
        $this->xhtml .= $node->getContent($this->filter);
        $this->registerForPostMethod($node);
    }

    /**
     * Close link tag.
     *
     * @param T_Text_InternalLink $node
     */
    function postTextInternalLink(T_Text_ExternalLink $node)
    {
        $this->xhtml .= '</a>';
    }

    /**
     * Visit a formatted text node.
     *
     * @param T_Text_Plain $node
     */
    function visitTextPlain(T_Text_Plain $node)
    {
        $this->xhtml .= $node->getContent($this->filter);
    }

    /**
     * Visit a header node.
     *
     * @param T_Text_Header $node
     */
    function visitTextHeader(T_Text_Header $node)
    {
        $level = $node->getLevel() + $this->header_shift;
        if ($level>6) {
            $this->xhtml .= EOL.'<p><strong>';
        } else {
            $this->xhtml .= EOL."<h$level>";
        }
        $this->xhtml .= $node->getContent($this->filter);
        $this->registerForPostMethod($node);
    }

    /**
     * Close header tag.
     *
     * @param T_Text_Paragraph $node
     */
    function postTextHeader(T_Text_Header $node)
    {
        $level = $node->getLevel() + $this->header_shift;
        if ($level>6) {
            $this->xhtml .= '</strong></p>';
        } else {
            $this->xhtml .= "</h$level>";
        }
    }

    /**
     * Render an embedded resource.
     *
     * At the moment, this renderer is setup to assume that all embedded resources are
     * images. However, this could be easily extended to handle video, and so on.
     *
     * @param T_Text_EmbeddedLink $element
     */
    function visitTextResource(T_Text_Resource $node)
    {
        $escape = new T_Filter_Xhtml();
        if ($node->isInternal()) {
            $url = $this->root_url->getUrl($escape).$node->getUrl($escape);
        } else {
            $url = $node->getUrl($escape);
        }
        $this->xhtml .= '<img src="'.$url.'" alt="'.$node->getContent($this->filter).'" />'.EOL;
    }

    /**
     * Visit table.
     *
     * @param T_Text_Table $node
     */
    function visitTextTable(T_Text_Table $node)
    {
        $this->xhtml .= EOL.'<table>';
        $this->registerForPostMethod($node);
    }

    /**
     * Close table tag.
     *
     * @param T_Text_Table $node
     */
    function postTextTable(T_Text_Table $node)
    {
        $this->xhtml .= EOL.'</table>';
    }

    /**
     * Visit table row.
     *
     * @param T_Text_TableRow $node
     */
    function visitTextTableRow(T_Text_TableRow $node)
    {
        $this->xhtml .= EOL.'<tr>';
        $this->registerForPostMethod($node);
    }

    /**
     * Close table row tag.
     *
     * @param T_Text_TableRow $node
     */
    function postTextTableRow(T_Text_TableRow $node)
    {
        $this->xhtml .= EOL.'</tr>';
    }

    /**
     * Visit table cell.
     *
     * @param T_Text_TableCell $node
     */
    function visitTextTableCell(T_Text_TableCell $node)
    {
        $this->xhtml .= EOL.($node->is(T_Text_TableCell::HEADER) ? '<th' : '<td').
                        ($node->getSpan()>1 ? ' colspan="'.(int) $node->getSpan().'"' : '' ).'>'.
                        $node->getContent($this->filter);
        $this->registerForPostMethod($node);
    }

    /**
     * Close table cell.
     *
     * @param T_Text_TableCell $node
     */
    function postTextTableCell(T_Text_TableCell $node)
    {
        $this->xhtml .= $node->is(T_Text_TableCell::HEADER) ? '</th>' : '</td>';
    }

    /**
     * Visit a divider.
     *
     * Since <hr/> tags are notoriously hard to style consistently across IE and
     * other browsers, the markup rendered is to enclose the <hr> tag in a div.
     * This means the div can be styled and the <hr> hidden.
     *
     * ~~~~~~ (CSS)
     * div.hr {
     *     height: 15px;
     *     background: #fff url(hr1.gif) no-repeat scroll center;
     * }
     * div.hr hr {
     *     display: none;
     * }
     * ~~~~~~
     *
     * @param T_Text_Divider $node
     */
    function visitTextDivider(T_Text_Divider $node)
    {
        $this->xhtml .= EOL.'<div class="hr"><hr /></div>';
    }

    /**
     * Adjust the rendered header level.
     *
     * For example, if T_Xhtml_Text::setHeaderAdjustment(1), level 1
     * headers are rendered with a <h2> tag, level 2 headers with a <h3> tag..
     *
     * @param int $shift_down
     */
    function setHeaderAdjustment($shift_down)
    {
        if ($shift_down < 0) {
            throw new InvalidArgumentException('headers can only be shifted down');
        }
        $this->header_shift = $shift_down;
        return $this;
    }

    /**
     * Pre-Child visitor event.
     */
    function preChildEvent()
    {
        $this->depth++;
    }

    /**
     * Post-Child visitor event.
     */
    function postChildEvent()
    {
        $this->depth--;
        /* now execute 'post' method */
        if (isset($this->parent[$this->depth])) {
            $parent = $this->parent[$this->depth];
            $name   = explode('_',get_class($parent));
            array_shift($name);
            $method = 'post'.implode('',$name);
            $this->$method($parent);
            unset($this->parent[$this->depth]);
        }
    }

    /**
     * Always traverse children.
     *
     * @return bool  whether to traverse composite children.
     */
    function isTraverseChildren()
    {
        return true;
    }

    /**
     * Return XHTML string.
     *
     * @return string
     */
    function __toString()
    {
        return $this->xhtml;
    }

}
