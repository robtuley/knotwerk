<?php
/**
 * Contains the T_Xhtml_UrlList class.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A rendered URL list view.
 *
 * This class can be used as a visitor for a URL composite, and renders the
 * result as a XHTML list.
 *
 * @package views
 * @license http://knotwerk.com/licence MIT
 */
class T_Xhtml_UrlList extends T_Xhtml_List
{

    /**
     * Default rendering of a node.
     *
     * In this case we want to render any instance of a T_Url_Leaf using
     * active class definition.
     *
     * @param T_CompositeLeaf $node
     */
    function renderDefault(T_CompositeLeaf $node)
    {
        $class = $this->getClass($node);
        if ($class) $this->addClass($node,$class);
        echo $node->__toString();
    }

    /**
     * Adds a class to the URL.
     *
     * @param T_CompositeLeaf $node
     * @param string $class
     * @return void
     */
    protected function addClass($node,$class)
    {
        $existing = $node->getAttribute('class');
        if (strlen($existing)>0) $existing .= ' '; // add space delimiter
        $existing .= $class;
        $node->setAttribute('class',$existing);
    }

    /**
     * Get the class of a node.
     *
     * @param T_CompositeLeaf $node
     */
    protected function getClass($node)
    {
        $class = false;
        if ($node instanceof T_Url_Leaf) {
            if ($node->isActive()) $class = 'cur ';
            $c = $node->getComposite();
            if ($c && $c->isChildren()) $class .= 'parent';
        }
        return $class ? trim($class) : false;
    }

    /**
     * Opens the list tag for a particular node.
     *
     * @param T_Composite $node  node being visited
     */
    protected function openListTag($node)
    {
        $class = $this->getClass($node);
        if ($class) $class = ' class="'.$class.'"';
        echo "<li{$class}>";
    }

}
