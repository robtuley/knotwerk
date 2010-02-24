<?php
/**
 * Contains the T_Url_Leaf class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * An XHTML URL Leaf.
 *
 * This class encapsulates an XHTML URL to a different resource, and can be used
 * as a 'leaf' in a composite URL construct. In other words, the URL can be part
 * of the composite construction, but cannot have any children.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Url_Leaf extends T_Url_Xhtml
                       implements T_CompositeLeaf,T_Visitorable
{

    /**
     * Parent of the current object.
     *
     * @var T_Composite
     */
    protected $parent = null;

    /**
     * Whether this link is "active".
     *
     * @var bool
     */
    protected $is_active = false;

    /**
     * Set the parent.
     *
     * This function sets a reference to the parent of the current object. This
     * enables actions to propagate up the tree. This is not a standard
     * composite feature, but is required for "active" menu flags to propagate.
     *
     * @param T_Composite $parent  the parent object of this instance
     */
    function setParent(T_Composite $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Whether the current object is on the active link tree path.
     *
     * @return bool  whether this URI is "active" related to the current page.
     */
    function isActive()
    {
        return $this->is_active;
    }

    /**
     * Set this URL as active.
     *
     * "Active" in this sense refers to, in the context of a menu list, the fact
     * that the page is linked to the current page view. All members of a
     * breadcrumb list are "active".
     */
    function setActive()
    {
        $this->is_active = true;
        if (isset($this->parent)) {
            $this->parent->setActive();
        }
        return $this;
    }

    /**
     * Gets the available composite object (null in this case).
     *
     * @return null  no composite available
     */
    function getComposite()
    {
        return null;
    }

    /**
     * Accept a visitor.
     *
     * @param T_Visitor $visitor  visitor object
     */
    function accept(T_Visitor $visitor)
    {
        $name   = explode('_',get_class($this));
        array_shift($name); // remove prefix
        $method = 'visit'.implode('',$name);
        $visitor->$method($this);
    }

}