<?php
/**
 * Defines the T_Form_Group class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates input element collection.
 *
 * @package forms
 */
class T_Form_Group implements T_Composite,T_Form_Input
{

    /**
     * Gets the clean value if available.
     *
     * @var mixed
     */
    protected $clean = null;

    /**
     * Children of current object.
     *
     * @var array
     */
    protected $children = array();

    /**
     * Element alias.
     *
     * @var string
     */
    protected $alias;

    /**
     * Element label.
     *
     * @var string
     */
    protected $label;

    /**
     * Array of collection filters.
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Error.
     *
     * @var T_Form_Error
     */
    protected $error = false;

    /**
     * Whether some input within this container is required.
     *
     * Input elements are required by default. A input collection is by default
     * optional, but can be set to required with T_Form_Group::setRequired().
     *
     * @var bool
     */
    protected $is_required = false;

    /**
     * Array of attributes.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Create empty collection.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     */
    function __construct($alias,$label)
    {
        $this->alias = (string) $alias;
        $this->label = (string) $label;
        if (strlen($alias)==0 || strlen($label)==0) {
            throw new InvalidArgumentException('zero length alias or label');
        }
    }

    /**
     * Whether the current object has any children.
     *
     * @return bool  whether there are any children
     */
    function isChildren()
    {
        return count($this->children)>0;
    }

    /**
     * Gets the available composite object (self in this case).
     *
     * @return T_Composite  self composite
     */
    function getComposite()
    {
        return $this;
    }

    /**
     * Add a child element.
     *
     * Can override default behaviour by specifying a named key for the child,
     * otherwise the element alias is used by default.
     *
     * @param T_CompositeLeaf $child  child input element object.
     * @return T_Form_Group  fluent interface
     */
    function addChild(T_CompositeLeaf $child,$key=null)
    {
        if (isset($key)) {
            $this->children[$key] = $child;
        } else {
            $this->children[$child->getAlias()] = $child;
        }
        return $this;
    }

    /**
     * Gets the first child.
     *
     * @return T_Form_Input
     */
    function getFirstChild()
    {
        return reset($this->children);
    }

    /**
     * Accept a visitor.
     *
     * @param T_Visitor $visitor  visitor object
     * @return T_Form_Group  fluent interface
     */
    function accept(T_Visitor $visitor)
    {
        $name   = explode('_',get_class($this));
        array_shift($name);
        $method = 'visit'.implode('',$name);
        $visitor->$method($this);
        if ($visitor->isTraverseChildren() && $this->isChildren()) {
            $visitor->preChildEvent();
            foreach ($this->children as $child) {
        	   $child->accept($visitor);
            }
            $visitor->postChildEvent();
        }
        return $this;
    }

    /**
     * Gets the value encapsulated by collection.
     *
     * @return mixed  collection object if available
     */
    function getValue()
    {
        return $this->clean;
    }

    /**
     * Sets the value encapsulated by the collection.
     *
     * @param mixed $clean  value of encapsulated collection
     * @return T_Form_Group  fluent interface
     */
    function setValue($clean)
    {
        $this->clean= $clean;
        return $this;
    }

    /**
     * Whether any components of the input has been submitted.
     *
     * @param T_Cage_Array $source  source array to check
     * @return bool  whether any inputs have been submitted
     */
    function isSubmitted(T_Cage_Array $source)
    {
        foreach ($this->children as $child) {
            if ($child->isSubmitted($source)) { return true; }
        }
        return false;
    }

    /**
     * Validates the entire collection.
     *
     * @param T_Cage_Array $source  source data
     * @return T_Form_Group  fluent interface
     */
    function validate(T_Cage_Array $source)
    {
        $this->clean = null;
        $this->error = false;
        foreach ($this->children as $child) {
            $child->validate($source);
        }
        if ($this->isPresent()) {
            try {
                foreach ($this->filters as $filter) {
                    _transform($this,$filter);
                }
            } catch (T_Exception_Filter $e) {
                $this->setError(new T_Form_Error($e->getMessage()));
                return $this;
            }
        } elseif (!$this->isPresent() && $this->isRequired()) {
            $this->setError(new T_Form_Error('is missing'));
        }
        return $this;
    }

    /**
     * Whether the entire collection is valid.
     *
     * @return bool  whether the collection is valid
     */
    function isValid()
    {
        $valid = $this->error===false;
        foreach ($this->children as $child) {
            if (!$child->isValid()) { $valid = false; }
        }
        return $valid;
    }

    /**
     * Whether any elements in collection are present.
     *
     * @return bool  whether any elements are present
     */
    function isPresent()
    {
        foreach ($this->children as $child) {
            if ($child->isPresent()) { return true; }
        }
        return false;
    }

    /**
     * Sets the fieldname salt for the entire collection.
     *
     * @param string $salt  fieldname salt value
     */
    function setFieldnameSalt($salt,T_Filter_RepeatableHash $hash)
    {
        foreach ($this->children as $child) {
            $child->setFieldnameSalt($salt,$hash);
        }
        return $this;
    }

    /**
     * Set collection as required.
     *
     * This sets the collection a required input, which means at least one of the
     * elements it contains must be present. It is useful when a number of optional
     * elements are encapsulated in a collection (e.g. fieldset), and you want at
     * least one filled in.
     *
     * @return T_Form_Group  fluent interface
     */
    function setRequired()
    {
        $this->is_required = true;
        return $this;
    }

    /**
     * Whether the collection is required or not.
     *
     * @return bool whether the collection is required or not.
     */
    function isRequired()
    {
        return $this->is_required;
    }

    /**
     * Search for element with a particular name.
     *
     * @param string $alias  alias to search for
     * @return bool|T_Form_Element  element required or false if not found
     */
    function search($alias)
    {
        // introspect
        if (strcmp($alias,$this->alias)===0) {
            return $this;
        }
        // examine children keys (may be different to alias, so need to include)
        if (isset($this->children[$alias])) {
            return $this->children[$alias];
        }
        // look within children
        foreach ($this->children as $child) {
            $element = $child->search($alias);
            if ($element !== false) {
                return $element;
            }
        }
        //
        return false;
    }

    /**
     * Gets the alias.
     *
     * @return string alias
     */
    function getAlias()
    {
        return $this->alias;
    }

    /**
     * Sets the alias.
     *
     * @return T_Form_Input  fluent interface
     */
    function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Get element label.
     *
     * @param function $f  filter to apply
     * @return string  element label
     */
    function getLabel($f=null)
    {
        return _transform($this->label,$f);
    }

    /**
     * Allows sub-portions of composite tree to be accessed by keywords.
     *
     * @param string $key  child keyword
     * @return OKT_HttpUrl  the tree sub-portion
     */
    function __get($key)
    {
        if (array_key_exists($key,$this->children)) {
            return $this->children[$key];
        } else {
            throw new InvalidArgumentException("child $key doesn't exist");
        }
    }

    /**
     * Gets the error that has occurred.
     *
     * @return bool|T_Form_Error  error experienced, or false if no error
     */
    function getError()
    {
        return $this->error;
    }

    /**
     * Set the error.
     *
     * @param $error T_Form_Error  error experienced
     * @return T_Form_Group  fluent interface
     */
    function setError(T_Form_Error $error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Clears any errors (includes children).
     *
     * @return T_Form_Element  fluent interface
     */
    function clearError()
    {
        $this->error = false;
        foreach ($this->children as $child) {
            $child->clearError();
        }
        return $this;
    }

    /**
     * Add a filter to apply.
     *
     * Filters can be applied to an element collection, and are executed
     * *after* the child validation. These are used for actions such as
     * comparing two different fields for equality, etc.
     *
     * @see T_Validate_Confirm
     * @param function $filter
     * @return T_Form_Group  fluent interface
     */
    function attachFilter($filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Removes a filter.
     *
     * @param function $filter  filter to remove
     * @return T_Form_Group  fluent interface
     */
    function removeFilter($filter)
    {
        foreach ($this->filters as $key => $f) {
        	if ($f === $filter) {
        	    unset($this->filters[$key]);
        	    return $this;
        	}
        }
        throw new InvalidArgumentException('filter not attached');
    }

    /**
     * Sets an attribute on a particular member.
     *
     * @param string $name  attribute name
     * @param mixed  $value  attribute value
     * @return T_Form_Element  fluent
     */
    function setAttribute($name,$value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Gets the value of an attribute.
     *
     * @param string $name   attribute name
     * @return mixed
     */
    function getAttribute($name)
    {
        return array_key_exists($name,$this->attributes) ? $this->attributes[$name] : null;
    }

    /**
     * Gets the values of all set attributes.
     *
     * @return array   attribute name=>value pairs
     */
    function getAllAttributes()
    {
        return $this->attributes;
    }

    /**
     * When cloning, make sure children are also cloned.
     */
    function __clone()
    {
        $children = array();
        foreach ($this->children as $key=>$c) $children[$key] = clone($c);
        $this->children = $children;
    }

}
