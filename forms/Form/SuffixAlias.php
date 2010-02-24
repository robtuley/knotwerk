<?php
/**
 * Contains the T_Form_SuffixAlias class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Modifies the alias by adding a suffix.
 *
 * @package forms
 */
class T_Form_SuffixAlias implements T_Visitor
{

    /**
     * Suffix.
     *
     * @var string
     */
    protected $suffix;

    /**
     * Create suffix.
     *
     * @param string $suffix
     */
    function __construct($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * Catches any visit method calls.
     *
     * @param string $method  method name that has been called (visit..)
     * @param array $arg  array of arguments
     */
    function __call($method,$arg)
    {
        $node = $arg[0];
        if ( $node instanceof T_Form_Input ) {
            $alias = $node->getAlias().$this->suffix;
            $node->setAlias($alias);
        }
    }

    // unused Event calls
    function preChildEvent() { }
    function postChildEvent() { }
    function isTraverseChildren()
    {
        return true;
    }


}
