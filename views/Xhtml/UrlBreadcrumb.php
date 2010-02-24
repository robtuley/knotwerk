<?php
/**
 * Contains the T_Xhtml_UrlBreadcrumb class.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A URL breadcrumb visitor.
 *
 * This class can be used as a visitor for a URL composite, rendering just the
 * active components in a nested XHTML list that can be styled as a 'breadcrumb'
 * list. This visitor assumes that there are only ONE active flagged
 * link per level, otherwise it will throw an exception.
 *
 * @package views
 * @license http://knotwerk.com/licence MIT
 */
class T_Xhtml_UrlBreadcrumb extends T_Xhtml_UrlList
{

    /**
     * Presentation prefix.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Set presentation separator.
     *
     * @param string $prefix  breadcrumb separator
     */
    function __construct($prefix = ' / ')
    {
        $this->prefix = (string) $prefix;
    }

    /**
     * Array to keep track if there has been an active link at this level.
     *
     * @var array
     */
    protected $num_uri = array(0 => 0);

    /**
     * Pre-Child visitor event.
     *
     * Note that we have disabled the opening of the <ul> tag here, as we know
     * that there will be a maximum of 1 active link per list.
     */
    function preChildEvent()
    {
        $this->depth++;
        $this->num_uri[$this->depth] = 0;
    }

    /**
     * Post-Child visitor event.
     *
     * Close the unordered list tag.
     *
     * @throws OutOfRangeException  if more than one active URI in a level
     */
    function postChildEvent()
    {
        if ($this->num_uri[$this->depth] == 1) {
            echo EOL.str_repeat($this->indent,$this->depth+1).'</li>'.
            EOL.str_repeat($this->indent,$this->depth).'</ul>';
        } elseif ($this->num_uri[$this->depth] > 1) {
            $msg  = "More than one active link at level {$this->depth}.";
            throw new OutOfRangeException($msg);
        }
        $this->depth--;
        if ($this->depth==0 && $this->num_uri[0] == 1) {
            // special case as never called again..
            echo EOL.$this->indent.'</li>'.EOL.'</ul>';
        }
    }

    /**
     * Whether we are rendering at the current level/node.
     *
     * In this case, we only want to render a node when it is marked as active.
     *
     * @param T_Composite $node  optional node if called in visit* routine
     * @return bool  whether to render
     */
    protected function isRender($node=null)
    {
        if (!is_null($node)) {
            return (parent::isRender($node) &&
                    $node instanceof T_Url_Leaf &&
                    $node->isActive());
        } else {
            return parent::isRender($node);
        }
    }

    /**
     * Called before a list item is rendered.
     *
     * @param T_Composite $node  node being visited
     */
    protected function preListItem($node)
    {
        $this->num_uri[$this->depth]++;
        echo EOL.str_repeat($this->indent,$this->depth).'<ul>'.
             EOL.str_repeat($this->indent,$this->depth+1).'<li>';
    }

    /**
     * Called after a list item is rendered.
     *
     * @param OKT_UriContainer $node  node being visited
     */
    protected function postListItem($node)
    {
        if ($this->depth==0 && !$this->hasChildren($node)) {
            // special case where method postChildEvent() never called.
            echo EOL.$this->indent.'</li>'.EOL.'</ul>';
        }
    }

    /**
     * Default rendering of a node.
     *
     * For a breadcrumb, we don't want to add active class (all the links here
     * are active!).
     *
     * @param T_CompositeLeaf $node
     */
    function renderDefault(T_CompositeLeaf $node)
    {
        echo $this->prefix;
        echo $node;
    }

}
