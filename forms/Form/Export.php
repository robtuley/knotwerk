<?php
/**
 * Contains the T_Form_Export class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Exports an array of form key=>value data.
 *
 * @package forms
 */
class T_Form_Export implements T_Visitor
{

    /**
     * Array of data.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Gets the data.
     *
     * @param function $filter  optional filter
     * @return array
     */
    function getData($filter=null)
    {
        return _transform($this->data,$filter);
    }

    /**
     * Catches default visit method calls.
     *
     * @param string $method  method name that has been called (visit..)
     * @param array $arg  array of arguments
     */
    function __call($method,$arg)
    {
        $node = $arg[0];
        if ($node instanceof T_Form_Container) {
            return $this->visitFormContainer($node);
        } elseif ($node instanceof T_Form_Group) {
            // nothing required, just elements are important
            return;
        } elseif ($node instanceof T_Form_Hidden) {
            return $this->visitFormHidden($node);
        }

        if ($node->isPresent() && $node->isValid()) {
            $this->data[$node->getFieldname()] = $node->getDefault();
            // ^ use default as "value" may have been post filtered to
            // object/diff value, etc.
        }
    }

    /**
     * Visit a form.
     *
     * @param T_Form_Container $node
     */
    function visitFormContainer(T_Form_Container $node)
    {
        foreach ($node->getActions() as $a) {
            if ($a->isPresent()) $this->data[$a->getFieldname()] = 1;
        }
    }

    /**
     * Visit a hidden input.
     *
     * @param T_Form_Hidden
     */
    function visitFormHidden(T_Form_Hidden $node)
    {
        $this->data[$node->getFieldname()] = $node->getFieldValue();
        $this->data[$node->getChecksumFieldname()] = $node->getChecksumFieldValue();
    }

    // no functionality needed
    function preChildEvent() { }
    function postChildEvent() { }
    function isTraverseChildren() { return true; }

}
