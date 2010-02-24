<?php
/**
 * Contains the class T_Exception_Controller.
 *
 * @package controllers 
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Controller Exception.
 *
 * A T_Exception_Controller error is thrown when an error is encountered
 * within the controller parsing and mapping process that cannot be attrbuted
 * to user error.
 *
 * @package controllers
 * @see OKT_exceptionHandler
 * @license http://knotwerk.com/licence MIT
 */
class T_Exception_Controller extends UnexpectedValueException
{
    // nothing extra defined yet.
}