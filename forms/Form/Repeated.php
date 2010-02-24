<?php
/**
 * Defines the T_Form_Repeated class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a repeatable element.
 *
 * @package forms
 */
class T_Form_Repeated extends T_Form_Group implements Countable,Iterator
{

    /**
     * Minimum number of elements that must be submitted.
     *
     * @var int
     */
    protected $min;

    /**
     * Iterator next component validity.
     *
     * @var bool
     */
    protected $valid = false;

    /**
     * Create repeatable group.
     *
     * @param string $alias
     * @param string $label
     * @param int $min  minimum number that must be submitted
     * @param int $max  maximum
     */
    function __construct($alias,$label,$min,$max)
    {
        parent::__construct($alias,$label);
        $this->min = (int) $min;
        for ($i=1; $i<=$max; $i++) {
            $this->children[] = new T_Form_Group($alias."_$i",$label);
        }
        if (count($this->children)==0) {
            throw new RuntimeException("maximum repetion $max must be greater than 0");
        }
    }

    /**
     * Whether the repeatable elements have any children.
     *
     * @return bool
     */
    function isChildren()
    {
        return reset($this->children)->isChildren();
          // ^ looks to see if any children have been added. We just need to
          //   examine the first children as all direct children groups are
          //   the same.
    }

    /**
     * Gets the available composite object (self in this case).
     *
     * @return T_Composite
     */
    function getComposite()
    {
        return $this;
    }

    /**
     * Add a child element.
     *
     * @param T_CompositeLeaf $child  child input element object.
     * @return T_Form_Group  fluent interface
     */
    function addChild(T_CompositeLeaf $child,$key=null)
    {
        if (!$key) $key = $child->getAlias();
        $i = 1;
        foreach ($this->children as $group) {
            $block = clone($child);
            $block->accept(new T_Form_SuffixAlias("_$i"));
            $group->addChild($block,$key);
              // ^ cloned and alias suffixed, and added to each group with
              //   original alias
            $i++;
        }
        return $this;
    }

    /**
     * Validates the repeatable set.
     *
     * The minimum number specified for the repeatable item is always validated.
     * After this point, the repeated items are only validated *if* they have
     * been submitted (they may contain required elements which would fail
     * validation incorrectly as the whole repeating block is not required).
     *
     * @param T_Cage_Array $source  source data
     * @return T_Form_Group  fluent interface
     */
    function validate(T_Cage_Array $source)
    {
        $this->clean = null;
        $this->error = false;
        $i = 1;
        foreach ($this->children as $child) {
            $validate = ($this->min>=$i);
            if (!$validate) $validate = $child->isSubmitted($source);
            if ($validate) {
                $child->validate($source);
            }
            $i++;
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
     * Search for element.
     *
     * Search in this case is a bit more tricky, since there are repeated
     * elements. So in this case, we simply search through the children and
     * return an array if the elements are part of the repeatable group.
     *
     * @param string $alias  alias to search for
     * @return bool|T_Form_Element|array  element required or false if not found
     */
    function search($alias)
    {
        // introspect
        if (strcmp($alias,$this->alias)===0) {
            return $this;
        }
        // search through children and return array if found
        $elements = array();
        foreach ($this->children as $child) {
            $e = $child->search($alias);
            if ($e!==false) $elements[] = $e;
        }
        if (count($elements)>0) return $elements;
        // not found
        return false;
    }

    /**
     * Gets the minimum required number of items.
     *
     * @param function $filter
     * @return int
     */
    function getMin($filter=null)
    {
        return _transform($this->min,$filter);
    }

    /**
     * Counts the number of submitted elements.
     *
     * @return int
     */
    function count()
    {
        $count = 0;
        foreach ($this->children as $child) {
            if ($child->isPresent()) $count++;
        }
        return $count;
    }

    // ITERATION (over just the submitted elements)

    function rewind()
    {
        $this->valid = (false !== reset($this->children));
        if ($this->valid() && !$this->current()->isPresent()) $this->next();
    }

    function current()
    {
        return current($this->children);
    }

    function key()
    {
        return key($this->children);
    }

    function next()
    {
        $this->valid = (false !== next($this->children));
        if ($this->valid() && !$this->current()->isPresent()) $this->next();
    }

    function valid()
    {
        return $this->valid;
    }

}
