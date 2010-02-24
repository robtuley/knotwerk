<?php
/**
 * Defines the T_Form_Password class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a user freeform password input.
 *
 * @package forms
 */
class T_Form_Password extends T_Form_Text
{

    /**
     * Create password element.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     */
    function __construct($alias,$label)
    {
        parent::__construct($alias,$label);
        // by default, should not redisplay any values
        $this->redisplayInvalid(false);
        $this->redisplayValid(false);
    }


}