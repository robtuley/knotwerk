<?php
/**
 * Unit test cases for T_Form_Password class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Form_Password unit tests.
 *
 * @package formTests
 */
class T_Test_Form_Password extends T_Test_Form_Text
{

    /**
     * Password does not redisplay invalid submissions by default.
     *
     * @return bool
     */
    function isRedisplayInvalidByDefault()
    {
        return false;
    }

    /**
     * Gets a new instance of the password element.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     * @return T_Form_Password  text input to test.
     */
    function getInputElement($alias,$label)
    {
        return new T_Form_Password($alias,$label);
    }

}