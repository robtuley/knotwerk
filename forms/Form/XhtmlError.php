<?php
/**
 * Contains the T_Form_XhtmlError class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A XHTML renderer that outputs a summary of any form errors.
 *
 * <code>
 * <div class="error">
 *   <p>There were some problems with your submission:</p>
 *   <ul>
 *     <li>Error #1</li>
 *     <li>Error #2</li>
 *   </ul>
 *   <p>In addition for security reasons the password field has not been
 *      re-populated and must be recompleted.</p>
 * </div>
 * </code>
 *
 * @package forms
 */
class T_Form_XhtmlError implements T_Visitor
{

    /**
     * Array of errors.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Array of fields that need to be re-completed.
     *
     * @var array
     */
    protected $recomplete = array();

    /**
     * Catches any visit method calls.
     *
     * @param string $method  method name that has been called (visit..)
     * @param array $arg  array of arguments
     */
    function __call($method,$arg)
    {
        $node = $arg[0];
        // include errors
        if ($node->getError() !== false) {
            $this->errors[] = $node;
        }
        // some elements need to be recompleted
        if ( ($node instanceof T_Form_Upload) ||
             ( ($node instanceof T_Form_Element) && !$node->isRedisplayValid() )
           ) {
            if ($node->isPresent() && $node->isValid()) $this->recomplete[] = $node;
        }
    }

    /**
     * Return XHTML string.
     *
     * @return string
     */
    function __toString()
    {
        $xhtml = '';
        if (count($this->errors)==0) return $xhtml;
        $f = new T_Filter_Xhtml();
        $xhtml = '<div class="error">'.EOL.
                       '    <p>There were some problems with your submission:</p>'.EOL.
                       '    <ul>'.EOL;
        foreach ($this->errors as $node) {
            $error = $node->getError();
            $msg = '';
            if (!($node instanceof T_Form_Container) && !($node instanceof T_Form_Hidden)) {
                $msg .= '<a href="#'.$node->getAlias().'">'.
                             $node->getLabel($f).'</a> ';
            }
            $msg .= _transform($error->getMessage(),$f);
            $xhtml .= '        <li>'.$msg.'</li>'.EOL;
        }
        $xhtml .= '    </ul>'.EOL;
        if (count($this->recomplete)>0) {
            $fields = array();
            foreach ($this->recomplete as $node) {
                $fields[] = '<a href="#'.$node->getAlias().'">'.$node->getLabel($f).'</a>';
            }
            $xhtml .= '    <p class="recomplete">In addition for security reasons the '.
                      implode(', ',$fields).' ';
            if (count($fields)>1) {
                $xhtml .= 'fields have been cleared and need';
            } else {
                $xhtml .= 'field has been cleared and needs';
            }
            $xhtml .= ' to be recompleted.</p>'.EOL;
        }
        $xhtml .= '</div>';
        return $xhtml;
    }

    /**
     * Recursively looks through nested array to visit all forms.
     *
     * @param mixed $target
     */
    function recursiveVisit($target)
    {
        if (is_array($target)) {
            foreach ($target as $t) {
                $this->recursiveVisit($t);
            }
        } else {
            if ($target && ($target instanceof T_Visitorable)) $target->accept($this);
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
