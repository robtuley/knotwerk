<?php
/**
 * Defines the T_Form_Get class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a form to retrieve a resource.
 *
 * @package forms
 */
class T_Form_Get extends T_Form_Container
{

    /**
     * Gets the submission method type.
     *
     * @return string  'get' method for resource retrieval
     */
    function getMethod()
    {
        return 'get';
    }

}