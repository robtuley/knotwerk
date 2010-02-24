<?php
/**
 * Contains the T_Xhtml_List class.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A rendered list visitor.
 *
 * This class can be used as a visitor for any composite, and renders the
 * result as a XHTML list.
 *
 * @package views
 * @license http://knotwerk.com/licence MIT
 */
class T_Xhtml_List implements T_Visitor
{

    /**
     * The depth in the current tree hierarchy.
     *
     * @var int
     */
    protected $depth = 0;

    /**
     * The maximum depth to be rendered.
     *
     * @var int
     */
    protected $max_depth = null;

    /**
     * Array of levels to exclude.
     *
     * @var array
     */
    protected $exclude = array();

    /**
     * Standard indent.
     *
     * @var string
     */
    protected $indent = '    ';

    /**
     * Sets the maximum depth to render.
     *
     * Depths start from 0. In other words 0==top level, 1==sub level, etc.
     *
     * @param int $max_depth  maximum depth to render to, 'null' to render all
     * @return T_Xhtml_List  fluent interface
     */
    function setMaxDepth($max_depth)
    {
        $this->max_depth = $max_depth;
        return $this;
    }

    /**
     * Sets the array of levels to exclude.
     *
     * Specify levels can be excluded from the render process, and can be set
     * here as an array of integer values. By default, no levels are excluded.
     *
     * @param array $exclude  levels to exclude
     * @return T_Xhtml_List  fluent interface
     */
    function setExcludeLevel(array $exclude)
    {
        $this->exclude = $exclude;
        return $this;
    }

    /**
     * Default rendering of a node.
     *
     * @param T_CompositeLeaf $node
     */
    function renderDefault(T_CompositeLeaf $node)
    {
        echo $node;
    }

    /**
     * Pre-Child visitor event.
     *
     * Lists of links are enclosed in an unordered list, so we need to open the
     * tag here.
     */
    function preChildEvent()
    {
        $this->depth++;
        if ($this->isRender()) {
            echo EOL.str_repeat($this->indent,$this->depth-1).'<ul>';
        }
    }

    /**
     * Post-Child visitor event.
     *
     * Close the unordered list tag.
     */
    function postChildEvent()
    {
        if ($this->isRender()) {
            echo EOL.str_repeat($this->indent,$this->depth-1).'</ul>';
        }
        $this->depth--;
        if ($this->isRender() && $this->depth>0) {
            echo EOL.str_repeat($this->indent,$this->depth).'</li>';
        }
    }

    /**
     * Whether we are rendering at the current level/node.
     *
     * @param T_Composite  optional node if called in visit* routine
     * @return bool  whether to render
     */
    protected function isRender($node=null)
    {
        $bydepth = (is_null($this->max_depth) || $this->depth<=$this->max_depth);
        return ($bydepth && !in_array($this->depth,$this->exclude));
    }

    /**
     * Stop traversing children after max depth.
     *
     * @return bool  whether to traverse composite children.
     */
    function isTraverseChildren()
    {
        return (is_null($this->max_depth) || $this->depth<$this->max_depth);
    }

    /**
     * Called before a list item is rendered.
     *
     * @param T_Composite  node being visited
     */
    protected function preListItem($node)
    {
        echo EOL.str_repeat($this->indent,$this->depth);
        if ($this->depth != 0) {
            $this->openListTag($node);
        }
    }

    /**
     * Opens the list tag for a particular node.
     *
     * @param T_Composite $node  node being visited
     */
    protected function openListTag($node)
    {
        echo '<li>';
    }

    /**
     * Called after a list item is rendered.
     *
     * @param T_CompositeLeaf  node being visited
     */
    protected function postListItem($node)
    {
        if ($this->depth != 0 && !$this->hasChildren($node)) {
            echo '</li>';
        }
    }

    /**
     * Does the node have children?
     *
     * @param T_CompositeLeaf  node being visited
     */
    protected function hasChildren($node)
    {
        if (!$this->isTraverseChildren()) {
            // not going to see any children, so return false.
            return false;
        }
        $composite = $node->getComposite();
        return (!is_null($composite) && $composite->isChildren());
    }

    /**
     * Renders a node through default renederer.
     *
     * @param string $method  method name that has been called (visit..)
     * @param array $arg  array of arguments
     */
    function __call($method,$arg)
    {
        // check method call
        if (strcmp(substr($method,0,5),'visit')!==0 || count($arg)!=1) {
            throw new BadFunctionCallException("Cannot handle call to $method");
        }
        $node = $arg[0];
        // push action to default renderer
        if ($this->isRender($node)) {
            $this->preListItem($node);
            $this->renderDefault($node);
            $this->postListItem($node);
        }
    }

    /**
     * Reset to defaults.
     *
     * @return T_Xhtml_List  fluent interface
     */
    function resetDefaults()
    {
        $this->setMaxDepth(null);
        $this->setExcludeLevel(array());
        return $this;
    }

}
